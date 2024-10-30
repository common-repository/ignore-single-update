<?php

namespace IGSPU;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
define( 'IGSPU_VERSION', '1.2.1' );
if ( !class_exists( 'IGSPU\\IGSPU' ) ) {
    class IGSPU {
        private $_path;

        private $_base_dir;

        private $_options;

        private $_default_settings;

        private $_settings;

        private $_is_plugin_screen;

        private $_screenID;

        private $_screenID_suffix = '';

        private $_datetime_format;

        private $_ignored_updates;

        private $_update_types = [];

        private $_ignored_counts = [
            'total'     => 0,
            'permanent' => 0,
        ];

        private $_has_updates;

        private $_vulnerabilities;

        private $_is_network_admin = false;

        private $_is_on;

        private $_plugin_file;

        private $_updates = [];

        function __construct() {
            $this->_init_settings();
            $this->_ignored_updates = $this->_options['plugins'] ?? [];
            $this->_ignored_counts = $this->_options['counts'] ?? $this->_ignored_counts;
            $this->_datetime_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
            $this->_has_updates = isset( get_site_transient( 'update_plugins' )->response );
            $this->_is_on = $this->_can_run_plugin_code();
            if ( $this->_has_updates ) {
                add_filter( 'site_transient_update_plugins', [$this, 'disable_plugin_updates'], 100 );
            }
            if ( is_admin() ) {
                $this->_set_backend_wp_hooks();
            }
        }

        private function _init_settings() : void {
            $this->_set_multisite_vars();
            $this->_set_paths();
            $this->_default_settings = $this->_get_default_settings();
            $this->_options = $this->_get_options();
            add_action( 'init', [$this, 'finalize_settings'] );
        }

        private function _set_paths() : void {
            $this->_path = plugins_url( '', IGSPU_PLUGIN_FILE );
            $this->_base_dir = dirname( plugin_basename( IGSPU_PLUGIN_FILE ) );
        }

        public function finalize_settings() : void {
            //Can't do it before, filter may not be available
            if ( apply_filters( "igspu_show_notice_days_setting", false ) ) {
                $this->_default_settings['notice_days'] = [
                    'type'         => 'number',
                    'tableRefresh' => false,
                    'unit'         => esc_html__( 'day(s)', 'ignore-single-update' ),
                    'value'        => 1,
                    'range'        => [1, 30],
                    'text'         => esc_html__( 'Days admin notices are dismissed for', 'ignore-single-update' ),
                    'desc'         => esc_html__( "When an admin notice is dismissed, it won't appear again for that number of days.", 'ignore-single-update' ),
                    'display'      => 'wordfence_obsolete',
                ];
            }
            $this->_settings = $this->_populate_settings();
        }

        private function _set_backend_wp_hooks() : void {
            if ( $this->_is_network_admin ) {
                add_action( 'network_admin_menu', [$this, 'register_menu_page'] );
                add_filter(
                    'network_admin_plugin_action_links',
                    [$this, 'plugin_action_links'],
                    10,
                    2
                );
            } elseif ( !is_multisite() ) {
                add_action( 'admin_menu', [$this, 'register_menu_page'] );
            }
            add_filter(
                'plugin_action_links',
                [$this, 'plugin_action_links'],
                10,
                2
            );
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_styles'] );
            add_action( 'admin_footer', [$this, 'enqueue_scripts'] );
            add_filter( 'igspu_multisite_needed_licenses', function () {
                return 1;
            } );
            add_filter( 'igspu_available_licenses', [$this, 'available_licenses'] );
            add_filter(
                'admin_url',
                [$this, 'admin_url'],
                10,
                2
            );
            if ( !$this->_is_on ) {
                return;
            }
            add_action( "wp_ajax_igspu_unignore_plugin_update", [$this, "unignore_update"] );
            add_action( "wp_ajax_igspu_refresh_table", [$this, "refresh_table"] );
            add_action( "wp_ajax_igspu_update_settings", [$this, "update_settings"] );
            if ( !$this->_has_updates ) {
                return;
            }
            if ( is_multisite() ) {
                add_action( 'network_admin_notices', [$this, 'admin_notice'] );
            } else {
                add_action( 'admin_notices', [$this, 'admin_notice'] );
            }
            add_action( 'wp_ajax_dismissed_notice_handler', [$this, "admin_notice_handler"] );
            add_filter( 'site_status_tests', [$this, 'add_health_tests'] );
            add_action( "wp_ajax_igspu_ignore_plugin_update", [$this, "ignore_update"] );
        }

        private function _set_multisite_vars() : void {
            if ( is_multisite() && is_super_admin() ) {
                $this->_screenID_suffix = '-network';
                $this->_is_network_admin = true;
            }
        }

        private function _get_options() : array {
            return get_site_option( IGSPU_OPTION, [
                'settings' => $this->_populate_settings(),
            ] );
        }

        private function _update_options( $options ) : void {
            update_site_option( IGSPU_OPTION, $options );
        }

        public function admin_notice_handler() : void {
            $this->_check_nonce();
            $type = sanitize_text_field( $_REQUEST['type'] );
            $options = $this->_options;
            $options['dismissed_notices'][$type] = date( 'Y-m-d', strtotime( '+' . absint( $_REQUEST['days'] ) . ' days' ) );
            $this->_update_options( $options );
            $this->_options = $options;
            wp_send_json_success();
            die;
        }

        private function _is_notice_active( $type ) : bool {
            if ( !isset( $this->_options['dismissed_notices'][$type] ) ) {
                return true;
            }
            if ( $this->_options['dismissed_notices'][$type] <= date( 'Y-m-d' ) ) {
                return true;
            }
            return false;
        }

        private function _user_can_access_settings() : bool {
            if ( $this->_is_network_admin ) {
                return true;
            }
            if ( is_multisite() ) {
                return false;
            }
            if ( current_user_can( 'administrator' ) || !apply_filters( 'igspu_restrict_settings_to_admin', false ) ) {
                return true;
            }
            return false;
        }

        public function admin_notice() : void {
            if ( in_array( $this->_screenID, ['plugins' . $this->_screenID_suffix, 'update-core' . $this->_screenID_suffix] ) && $this->_settings['notices'] != 'disabled' ) {
                if ( !$this->_ignored_counts['permanent'] && !$this->_ignored_counts['total'] ) {
                    return;
                }
                $actionText = sprintf( wp_kses( __( '<a href="%s">Review your ignored updates</a> to ensure that it is really needed.', 'ignore-single-update' ), [
                    'a' => [
                        'href' => [],
                    ],
                ] ), esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates' ) ) );
                if ( $this->_ignored_counts['permanent'] ) {
                    $count = 'permanent';
                    $text = _n(
                        'You are currently permanently ignoring updates for %s plugin.',
                        'You are currently permanently ignoring updates for %s plugins.',
                        $this->_ignored_counts[$count],
                        'ignore-single-update'
                    );
                    $noticeType = 'error';
                } else {
                    $noticeType = 'warning';
                    $count = 'total';
                    $text = _n(
                        'You are currently ignoring %s plugin update.',
                        'You are currently ignoring %s plugin updates.',
                        $this->_ignored_counts[$count],
                        'ignore-single-update'
                    );
                }
                if ( $this->_is_notice_active( $noticeType ) && ($noticeType == 'error' || $this->_settings['notices'] != 'critical') ) {
                    ?>
                    <div class="notice ispu-notice notice-<?php 
                    echo esc_attr( $noticeType );
                    if ( $noticeType == 'warning' || apply_filters( "igspu_dismissible_critical_notice", false ) ) {
                        echo ' is-dismissible';
                    }
                    ?>"
                         data-notice="<?php 
                    echo esc_attr( $noticeType );
                    ?>"
                         data-count="<?php 
                    echo esc_attr( $this->_ignored_counts[$count] );
                    ?>"
                         data-noticedays="<?php 
                    echo esc_attr( $this->_settings['notice_days'] );
                    ?>">
                        <p><?php 
                    echo sprintf( esc_html( $text ), '<span class="ispu-counter">' . $this->_ignored_counts[$count] . '</span>' ) . ' ' . $actionText;
                    ?>
                        </p>
                    </div>
                <?php 
                }
                ?>
            <?php 
            }
        }

        private function _set_screen_vars() : void {
            $this->_screenID = get_current_screen()->id;
            if ( !in_array( $this->_screenID, ['plugins' . $this->_screenID_suffix, 'update-core' . $this->_screenID_suffix, 'plugins_page_ignored-plugin-updates' . $this->_screenID_suffix] ) ) {
                $this->_is_plugin_screen = false;
            } else {
                $this->_is_plugin_screen = true;
            }
        }

        private function _populate_settings() : array {
            $settings = [];
            foreach ( $this->_default_settings as $optionName => $data ) {
                if ( !isset( $this->_options['settings'][$optionName] ) ) {
                    $settings[$optionName] = $data['value'];
                } else {
                    $settings[$optionName] = $this->_options['settings'][$optionName];
                }
            }
            $settings['wordfence_configured'] = $settings['wordfence_configured'] ?? false;
            return $settings;
        }

        public function add_health_tests( $tests ) : array {
            $healthTests = ['permanently_ignored_plugins', 'lengthy_ignored_plugins'];
            foreach ( $healthTests as $healthTest ) {
                $wrapper = function () use($healthTest) {
                    return $this->_health_test( $healthTest );
                };
                $tests['direct'][$healthTest] = [
                    'test' => $wrapper,
                ];
            }
            return $tests;
        }

        private function _health_test( $test ) : array {
            $result = $this->_run_health_test( $test );
            if ( $result ) {
                $pluginNames = $result;
                $results['badge'] = [
                    'label' => esc_html__( 'Security' ),
                ];
                $descriptionText = [
                    'Intro'      => '',
                    'Conclusion' => '',
                ];
                $dashiconClass = '';
                $stopIgnoringActionText = '<p><a href="' . esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates' ) ) . '">' . esc_html__( 'Stop ignoring the updates', 'ignore-single-update' ) . '</a></p>';
                $extraActionText = '<p><a style="background:#2196F3;color:#fff;padding:5px 10px;text-decoration: none;margin-top:10px;border-radius:5px" href="' . esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates&tab=plans' ) ) . '">' . esc_html__( 'Upgrade to Premium and never worry about it again', 'ignore-single-update' ) . '</a></p>';
                if ( $test == 'permanently_ignored_plugins' ) {
                    $results['status'] = 'critical';
                    //label is escaped in site-health-info.php
                    //We can't escape it here too to prevent double encoding
                    $results['label'] = __( 'You should never permanently ignore plugin updates', 'ignore-single-update' );
                    $results['badge']['color'] = 'red';
                    $results['actions'] = $stopIgnoringActionText . $extraActionText;
                    $descriptionText['Intro'] = wp_kses( _n(
                        'The following plugin <strong>cannot currently be updated</strong>:',
                        'The following plugins <strong>cannot currently be updated</strong>:',
                        count( $pluginNames ),
                        'ignore-single-update'
                    ), [
                        'strong' => [],
                    ] );
                    $descriptionText['Conclusion'] = esc_html__( 'You should never completely ignore future plugin updates, since they may contain important security fixes.', 'ignore-single-update' );
                    $dashiconClass = 'error';
                } elseif ( $test == 'lengthy_ignored_plugins' ) {
                    $results['status'] = 'recommended';
                    $results['label'] = __( 'You should avoid ignoring plugin updates for too long', 'ignore-single-update' );
                    $results['badge']['color'] = 'blue';
                    $results['actions'] = $stopIgnoringActionText . $extraActionText;
                    $descriptionText['Intro'] = wp_kses( _n(
                        'The currently available update for the following plugin <strong>is ignored for too long</strong>:',
                        'The currently available updates for the following plugins <strong>are ignored for too long</strong>:',
                        count( $pluginNames ),
                        'ignore-single-update'
                    ), [
                        'strong' => [],
                    ] );
                    $descriptionText['Conclusion'] = esc_html__( "You should avoid ignoring plugin updates for too long. If the update was problematic, a fix would most likely occur within 7 days.", 'ignore-single-update' );
                    $dashiconClass = 'warning';
                }
                $description = '<p>' . $descriptionText['Intro'] . '</p>';
                $description .= '<ul>';
                foreach ( $pluginNames as $pluginName ) {
                    $description .= '<li><span class="dashicons ' . $dashiconClass . '"></span><em>' . $pluginName . '</em></li>';
                }
                $description .= '</ul>';
                $description .= '<p>' . $descriptionText['Conclusion'] . '</p>';
                $results['description'] = $description;
                $results['test'] = $test;
                $result = $results;
            }
            return $result;
        }

        private function _run_health_test( $test ) : array {
            $pluginNames = [];
            $ignoredUpdates = $this->_ignored_updates;
            foreach ( $ignoredUpdates as $ignoredUpdate ) {
                if ( $ignoredUpdate['ignored_version'] == 'Any' && $test == 'permanently_ignored_plugins' ) {
                    $pluginNames[] = $ignoredUpdate['name'];
                } elseif ( $test == 'lengthy_ignored_plugins' && $ignoredUpdate['until'] && $ignoredUpdate['until'] > strtotime( '+7 days', time() ) ) {
                    $pluginNames[] = $ignoredUpdate['name'];
                }
            }
            return $pluginNames;
        }

        public function register_menu_page() : void {
            $count = $this->_ignored_counts['total'];
            if ( !$count ) {
                $count = 0;
            }
            $counter = ' <span class="update-plugins count-' . $count . '"><span class="update-count">' . $count . '</span></span>';
            add_submenu_page(
                'plugins.php',
                esc_html__( 'Ignored Plugin Updates', 'ignore-single-update' ),
                esc_html__( 'Ignored Updates', 'ignore-single-update' ) . $counter,
                'update_plugins',
                'ignored-plugin-updates',
                [$this, 'settings_page'],
                10
            );
        }

        private function _update_ignored_updates( $ignoredUpdates ) : void {
            $options = $this->_get_options();
            $this->_ignored_updates = $ignoredUpdates;
            $options['plugins'] = $ignoredUpdates;
            $this->_update_options( $options );
        }

        private function _check_nonce() : void {
            if ( !isset( $_REQUEST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'igspu_nonce' ) ) {
                wp_send_json_error();
                die;
            }
        }

        public function plugin_action_links( $links, $file ) : array {
            if ( $this->_base_dir . '/ignore-single-update.php' == $file ) {
                if ( $this->_user_can_access_settings() ) {
                    $settingsLink = '<a href="' . esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates&tab=settings' ) ) . '">' . esc_html__( 'Settings' ) . '</a>';
                    $links = array_merge( [$settingsLink], $links );
                }
                if ( igspu_fs()->is_not_paying() && (!is_multisite() || is_network_admin()) ) {
                    $upgradeLink = '<a style="font-weight:bold; color:#049443 !important" href="' . esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates&tab=plans' ) ) . '">';
                    if ( igspu_fs()->is_trial_utilized() ) {
                        $upgradeLink .= esc_html__( 'Upgrade' );
                    } else {
                        $upgradeLink .= esc_html__( 'Free Trial', 'ignore-single-update' );
                    }
                    $upgradeLink .= '</a>';
                    $links = array_merge( [$upgradeLink], $links );
                }
            }
            return $links;
        }

        public function settings_page() : void {
            require_once __DIR__ . '/../templates/plugin-page.php';
        }

        private function _render_info() : void {
            require_once __DIR__ . '/../templates/info-tab.php';
        }

        public function refresh_table() : void {
            $this->_check_nonce();
            wp_send_json_success( $this->render_table( $_REQUEST['force_refresh'] ) );
        }

        private function _render_settings() : void {
            require_once __DIR__ . '/../templates/settings-tab.php';
        }

        public function render_table( $forceRefresh = false ) {
            ob_start();
            require_once __DIR__ . '/../templates/table.php';
            return ob_get_clean();
        }

        private function _render_pricing( $forcedPlan = '' ) : void {
            require_once __DIR__ . '/../templates/plans.php';
        }

        private function _render_table_body( $updates, $type ) : void {
            require __DIR__ . '/../templates/list-tab.php';
        }

        public function enqueue_styles() : void {
            $this->_set_screen_vars();
            if ( !$this->_is_plugin_screen ) {
                return;
            }
            if ( $this->_screenID == 'plugins_page_ignored-plugin-updates' . $this->_screenID_suffix ) {
                wp_enqueue_style(
                    'igspu_options',
                    $this->_path . '/css/admin.css',
                    [],
                    IGSPU_VERSION
                );
                if ( $this->_ignored_updates ) {
                    add_thickbox();
                }
            } elseif ( !$this->_has_updates ) {
                return;
            }
            wp_enqueue_style( 'sweetalert', $this->_path . '/res/swal/swal-themes/' . $this->_settings['theme'] . '.css' );
            if ( !$this->_is_on ) {
                return;
            }
            $inlineStyles = '.ignore-all-versions {cursor:pointer;text-decoration:underline;font-size:60%;float:right;}';
            if ( in_array( $this->_screenID, ['update-core' . $this->_screenID_suffix, 'plugins' . $this->_screenID_suffix] ) ) {
                $inlineStyles .= '
                .swal2-title{margin:0!important;line-height:30px}
                body .swal2-html-container{text-align:left}
                .plugin-title .notice-error{margin-top:10px}
                .ispu-warning{color:red}
                .ispu-warning-title{font-weight:bold}';
            }
            wp_add_inline_style( 'sweetalert', $inlineStyles );
        }

        public function enqueue_scripts() : void {
            if ( !$this->_is_plugin_screen ) {
                return;
            }
            $localizationTexts = [
                'AreYouSure'   => esc_html__( 'Are you sure?', 'ignore-single-update' ),
                'OKButton'     => esc_html__( 'OK' ),
                'CancelButton' => esc_html__( 'Cancel' ),
            ];
            $localization = [
                'nonce' => wp_create_nonce( "igspu_nonce" ),
            ];
            $theme = $this->_settings['theme'];
            if ( $this->_screenID == 'plugins_page_ignored-plugin-updates' . $this->_screenID_suffix ) {
                $settings = $this->_default_settings;
                if ( $this->_ignored_updates ) {
                    wp_enqueue_script( 'plugin-install' );
                }
                $scriptFile = 'settings.js';
                $localizationTexts = array_merge( $localizationTexts, [
                    'SettingsUpdateSuccess' => esc_html__( 'Settings successfully updated', 'ignore-single-update' ),
                    'ConfirmDisableNotices' => esc_html__( 'We do not recommend completely disabling notices, since you may forget you ignored updates.', 'ignore-single-update' ),
                    'HowAboutCritical'      => esc_html__( 'How about only showing critical notices?', 'ignore-single-update' ),
                    'DisableButton'         => esc_html__( 'Disable all', 'ignore-single-update' ),
                    'DenyButton'            => esc_html__( 'Critical only', 'ignore-single-update' ),
                ] );
                $localization = array_merge( $localization, [
                    'update_core_url' => esc_url( admin_url( 'update-core.php' ) ),
                ] );
            } else {
                $settings = $this->_settings;
                unset($settings['theme']);
                $localization['screen_ids'] = [
                    'update_core' => 'update-core' . $this->_screenID_suffix,
                    'plugins'     => 'plugins' . $this->_screenID_suffix,
                ];
                $scriptFile = 'admin.js';
                $localizationTexts = array_merge( $localizationTexts, [
                    'Plugins'                      => esc_html__( 'Plugins' ),
                    'PluginsUpToDate'              => esc_html__( 'Your plugins are all up to date.' ),
                    'ConfirmDays'                  => esc_html__( 'For how many days do you want to ignore version %s?', 'ignore-single-update' ),
                    'UntilNext'                    => esc_html__( 'until next version', 'ignore-single-update' ),
                    'IgnoreAllVersions'            => esc_html__( 'Ignore all future versions', 'ignore-single-update' ),
                    'IgnoredUntilNextVersionToast' => esc_html__( 'Successfully ignored until next version', 'ignore-single-update' ),
                    'ConfirmError'                 => esc_html__( 'Only integers are accepted', 'ignore-single-update' ),
                    'Ignore'                       => esc_html__( 'Ignore update', 'ignore-single-update' ),
                    'ConfirmIgnoreAllVersions'     => esc_html__( 'Permanently ignoring future versions could lead to security issues.', 'ignore-single-update' ),
                    'IgnoredForeverToast'          => esc_html__( 'Successfully ignored forever', 'ignore-single-update' ),
                    'ErrorToast'                   => esc_html__( 'An error occurred. Refresh the page and try again.', 'ignore-single-update' ),
                    'IgnoredToastSingle'           => esc_html__( 'Successfully ignored for 1 day', 'ignore-single-update' ),
                    'IgnoredToastPlural'           => esc_html__( 'Successfully ignored for %s days', 'ignore-single-update' ),
                ] );
                if ( $this->_screenID == 'update-core' . $this->_screenID_suffix ) {
                    $expiredText = [];
                    foreach ( $this->_ignored_updates as $pluginFile => $data ) {
                        if ( $data['until'] == 'Forever' || !$data['since'] ) {
                            continue;
                        }
                        $days = $this->_get_difference_in_days( $data['since'], $data['until'] );
                        if ( $days ) {
                            if ( isset( $data['update_type'] ) ) {
                                $expiredText[$pluginFile] = lcfirst( $this->_get_previous_ignored_text( $days, true ) );
                            } else {
                                $expiredText[$pluginFile] = lcfirst( $this->_get_previous_ignored_text( $days ) );
                            }
                        }
                    }
                    $localization = array_merge( $localization, [
                        'ignored_updates' => $this->_ignored_updates,
                        'expired_text'    => $expiredText,
                    ] );
                }
            }
            $settings['theme']['value'] = $theme;
            $settings['theme']['path'] = $this->_path . '/res/swal/swal-themes/';
            $localization = array_merge( $localization, [
                'screen_id'            => $this->_screenID,
                'settings'             => $settings,
                'updateTypes'          => $this->_update_types,
                'text'                 => $localizationTexts,
                'can_use_premium_code' => false,
            ] );
            if ( $scriptFile == 'settings.js' || $this->_has_updates ) {
                wp_enqueue_script(
                    'sweetalert',
                    $this->_path . '/res/swal/sweetalert2.min.js',
                    [],
                    '11.4.8'
                );
                wp_enqueue_script(
                    'igspu',
                    $this->_path . '/js/' . $scriptFile,
                    ['sweetalert', 'jquery'],
                    IGSPU_VERSION
                );
                wp_localize_script( 'igspu', 'IGSPU', $localization );
                wp_enqueue_script(
                    'igspu-counters',
                    $this->_path . '/js/counter-update.js',
                    ['igspu'],
                    IGSPU_VERSION
                );
            }
        }

        private function _get_previous_ignored_text( $days, $is_automatic = false ) : string {
            return sprintf( esc_html( _n(
                'This version was previously ignored for %s day',
                'This version was previously ignored for %s days',
                $days,
                'ignore-single-update'
            ) ), $days );
        }

        private function _autopilot_description() : string {
            ob_start();
            ?>
            <?php 
            esc_html_e( 'Activate it to automatically delay the apparition of new updates for your chosen amount of days.', 'ignore-single-update' );
            ?>
            <p><?php 
            esc_html_e( 'Given a versioning of "X.Y.Z":', 'ignore-single-update' );
            ?></p>
            <ul>
                <li><?php 
            printf( esc_html__( 'Choose "%s" to automatically ignore any change in the X number', 'ignore-single-update' ), esc_html__( 'Major versions', 'ignore-single-update' ) );
            ?></li>
                <li><?php 
            printf( esc_html__( 'Choose "%s" to automatically ignore any change in the X or Y number', 'ignore-single-update' ), esc_html__( 'Major + Minor versions', 'ignore-single-update' ) );
            ?></li>
                <li><?php 
            printf( esc_html__( 'Choose "%s" to automatically ignore any version change', 'ignore-single-update' ), esc_html__( 'Any version (Major + Minor + Patch)', 'ignore-single-update' ) );
            ?></li>
            </ul>
            <?php 
            return ob_get_clean();
        }

        private function _get_difference_in_days( $date1, $date2 ) {
            if ( !$date1 || !$date2 ) {
                return 0;
            }
            $dt = new \DateTime();
            $date1 = $dt->setTimestamp( $date1 );
            $dt = new \DateTime();
            $date2 = $dt->setTimestamp( $date2 );
            $interval = $date1->diff( $date2 );
            return $interval->days;
        }

        private function _counter_update_script( $count ) : void {
            ?>
            <script>
                newTotalCount = parseInt(<?php 
            echo (int) $count;
            ?>, 10);
                if (knownTotalCount === undefined) {
                    knownTotalCount = newTotalCount;
                }
                if (newTotalCount !== knownTotalCount) {
                    let diff = knownTotalCount - newTotalCount;
                    knownTotalCount = newTotalCount;
                    igspu_update_common_counters(diff);
                }
            </script>
        <?php 
        }

        public function ignore_update() : void {
            $this->_check_nonce();
            $ignoredUpdates = $this->_ignored_updates;
            $until = '';
            $duration = sanitize_text_field( $_REQUEST['duration'] );
            $ignoredVersion = sanitize_text_field( $_REQUEST['version'] );
            $counts = $this->_ignored_counts;
            if ( $duration == 'Forever' ) {
                $ignoredVersion = 'Any';
                $until = $duration;
                $counts['permanent']++;
            } elseif ( $duration >= 5000 ) {
                $until = 'Forever';
            } elseif ( $duration != '0' ) {
                $until = strtotime( '+' . absint( $duration ) . ' days', time() );
            }
            $counts['total']++;
            $this->_update_ignored_counts( $counts );
            $pluginFile = sanitize_text_field( $_REQUEST['plugin'] );
            $ignoredUpdates[$pluginFile] = [
                'name'            => sanitize_text_field( $_REQUEST['name'] ),
                'ignored_version' => $ignoredVersion,
                'until'           => $until,
                'since'           => time(),
                'slug'            => $this->_get_plugin_slug( $pluginFile ),
                'ignore_type'     => 'manual',
            ];
            $this->_update_ignored_updates( $ignoredUpdates );
            wp_send_json_success();
            die;
        }

        private function _get_plugin_slug( $pluginFile ) : string {
            return explode( '/', $pluginFile )[0];
        }

        public function update_settings() : void {
            $this->_check_nonce();
            $value = $_REQUEST['value'];
            if ( $value == 'false' ) {
                $value = false;
            } elseif ( $value == 'true' ) {
                $value = true;
            } elseif ( is_array( $value ) ) {
                $value = map_deep( $value, 'sanitize_text_field' );
            } else {
                $value = sanitize_text_field( $value );
            }
            $settings = $this->_settings;
            $optionName = sanitize_text_field( $_REQUEST['setting'] );
            if ( is_array( $optionName ) ) {
                // Legacy versioning handling
                foreach ( $optionName as $name ) {
                    $settings[$name] = $value;
                }
            } else {
                $settings[sanitize_text_field( $_REQUEST['setting'] )] = $value;
            }
            $ignoredUpdates = $this->_ignored_updates;
            $tableRefreshNeeded = false;
            $options = $this->_get_options();
            $options['plugins'] = $ignoredUpdates;
            $options['settings'] = $settings;
            $this->_settings = $settings;
            $this->_ignored_updates = $ignoredUpdates;
            $this->_update_options( $options );
            wp_send_json_success( $tableRefreshNeeded );
            die;
        }

        public function unignore_update() : void {
            $this->_check_nonce();
            $ignoredUpdates = $this->_ignored_updates;
            $plugin = sanitize_text_field( $_REQUEST['plugin'] );
            $type = sanitize_text_field( $_REQUEST['type'] );
            if ( $type == 'delete-all' ) {
                $plugins = explode( ',', $plugin );
                foreach ( $plugins as $plugin ) {
                    unset($ignoredUpdates[$plugin]);
                }
            } else {
                if ( $type != 'expired' ) {
                    $counts = $this->_ignored_counts;
                    if ( $type == 'permanent' ) {
                        $counts['permanent']--;
                    }
                    $counts['total']--;
                    $this->_update_ignored_counts( $counts );
                }
                unset($ignoredUpdates[$plugin]);
            }
            $this->_update_ignored_updates( $ignoredUpdates );
            wp_send_json_success();
            die;
        }

        private function _new_version_type( $currentVersion, $newVersion ) : string {
            $currentVersionNumbers = $this->_get_exploded_version( $currentVersion );
            $newVersionNumbers = $this->_get_exploded_version( $newVersion );
            $iterations = ['1', '2', '3'];
            foreach ( $newVersionNumbers as $index => $newVersionNumber ) {
                if ( !isset( $iterations[$index] ) ) {
                    return '3';
                }
                $currentVersionNumbers[$index] = $currentVersionNumbers[$index] ?? '0';
                if ( $currentVersionNumbers[$index] < $newVersionNumber ) {
                    return $iterations[$index];
                }
            }
            return '3';
        }

        private function _str_ends_with( $haystack, $needle ) : bool {
            if ( PHP_MAJOR_VERSION < 8 ) {
                return empty( $needle ) || substr( $haystack, -strlen( $needle ) ) === $needle;
            }
            return \str_ends_with( $haystack, $needle );
        }

        private function _str_contains( $haystack, $needle ) : bool {
            if ( PHP_MAJOR_VERSION < 8 ) {
                return empty( $needle ) || strpos( $haystack, $needle ) !== false;
            }
            return \str_contains( $haystack, $needle );
        }

        private function _get_exploded_version( $version ) : array {
            $version = trim( $version );
            while ( $this->_str_ends_with( $version, '.0' ) ) {
                $version = substr( $version, 0, -2 );
            }
            return explode( '.', $version );
        }

        private function _date_limit_reached( $pluginFile ) : bool {
            if ( $this->_ignored_updates[$pluginFile]['until'] && $this->_ignored_updates[$pluginFile]['until'] < time() ) {
                return true;
            }
            return false;
        }

        private function _maybe_update_latest_known_versions( $latestVersions ) : void {
            $shouldUpdate = false;
            $ignoredUpdates = $this->_ignored_updates;
            foreach ( $ignoredUpdates as $pluginFile => $data ) {
                if ( !isset( $latestVersions[$pluginFile] ) ) {
                    continue;
                }
                if ( !isset( $data['latest_known_version'] ) || $data['latest_known_version'] != $latestVersions[$pluginFile] ) {
                    $ignoredUpdates[$pluginFile]['latest_known_version'] = $latestVersions[$pluginFile];
                    $shouldUpdate = true;
                }
            }
            if ( $shouldUpdate ) {
                $this->_update_ignored_updates( $ignoredUpdates );
            }
        }

        private function _maybe_update_ignored_counts( $counts ) : void {
            if ( $this->_ignored_counts != $counts ) {
                $this->_update_ignored_counts( $counts );
            }
        }

        private function _update_ignored_counts( $counts ) : void {
            $options = $this->_get_options();
            $this->_ignored_counts = $counts;
            $options['counts'] = $counts;
            $this->_update_options( $options );
        }

        public function disable_plugin_updates( $value, $forceRefresh = false ) {
            if ( !isset( $value->response ) || !$value->response || !$this->_is_on ) {
                return $value;
            }
            if ( !$this->_updates ) {
                $this->_updates = $value->response;
            }
            $ignored_counts = [
                'total'     => 0,
                'permanent' => 0,
            ];
            $ignoredUpdates = $this->_ignored_updates;
            $latestVersions = [];
            if ( !function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $pluginList = get_plugins();
            $pluginsWithIgnoreLink = [];
            foreach ( $value->response as $pluginFile => $newVersionData ) {
                $this->_plugin_file = $pluginFile;
                $currentVersion = $pluginList[$pluginFile]['Version'];
                if ( $currentVersion == $newVersionData->new_version ) {
                    unset($value->response[$pluginFile]);
                    continue;
                }
                //Is it a patch, a minor version, or a major version?
                $updateType = $this->_new_version_type( $currentVersion, $newVersionData->new_version );
                $this->_update_types[$pluginFile] = $updateType;
                $latestVersions[$pluginFile] = [
                    'version' => $newVersionData->new_version,
                ];
                // Is it permanently blocked?
                if ( $this->_is_permanently_blocked() ) {
                    $ignored_counts['total']++;
                    $ignored_counts['permanent']++;
                    unset($value->response[$pluginFile]);
                    continue;
                }
                //Did we allow to ignore patch versions? Ex: 4.2.x
                if ( $this->_settings['patch_versions'] && $updateType == '3' ) {
                    continue;
                }
                //Plugin not known as ignored, onto the next
                if ( !isset( $ignoredUpdates[$pluginFile] ) ) {
                    $pluginsWithIgnoreLink[$pluginFile] = $newVersionData;
                    continue;
                }
                //Is it a newer version than the one we ignored? If yes, onto the next
                if ( $ignoredUpdates[$pluginFile]['ignored_version'] != $newVersionData->new_version ) {
                    $pluginsWithIgnoreLink[$pluginFile] = $newVersionData;
                    continue;
                }
                //Have we reached the number of days?
                if ( !$this->_date_limit_reached( $pluginFile ) ) {
                    $ignored_counts['total']++;
                    unset($value->response[$pluginFile]);
                    continue;
                }
                //Plugin does not have a newer version than the one ignored, and time has elapsed. We mention it. It can be ignored again
                $pluginsWithIgnoreLink[$pluginFile] = $newVersionData;
                if ( !wp_doing_ajax() ) {
                    add_action(
                        "in_plugin_update_message-" . $pluginFile,
                        [$this, 'set_expired_in_plugin_update_message'],
                        11,
                        2
                    );
                }
            }
            if ( !wp_doing_ajax() ) {
                $adminHook = 'plugin_action_links_';
                foreach ( $pluginsWithIgnoreLink as $pluginFile => $newVersionData ) {
                    $pluginName = $ignoredUpdates[$pluginFile]['name'] ?? $pluginList[$pluginFile]['Name'];
                    add_filter( $adminHook . $pluginFile, function ( $links_array ) use($pluginFile, $newVersionData, $pluginName) {
                        $links_array['ignore'] = ' <a class="ignore-version" href="javascript:void(0)" data-plugin="' . $pluginFile . '" data-version="' . $newVersionData->new_version . '" data-slug="' . $newVersionData->slug . '" data-name="' . $pluginName . '">' . esc_html__( 'Ignore update', 'ignore-single-update' ) . '</a>';
                        return $links_array;
                    } );
                }
                $this->_maybe_update_latest_known_versions( $latestVersions );
                $this->_maybe_update_ignored_counts( $ignored_counts );
            }
            return $value;
        }

        private function _is_permanently_blocked() : bool {
            $pluginFile = $this->_plugin_file;
            $ignoredUpdates = $this->_ignored_updates;
            return $this->_settings['permanent'] && isset( $ignoredUpdates[$pluginFile]['ignored_version'] ) && $ignoredUpdates[$pluginFile]['ignored_version'] == 'Any';
        }

        public function set_expired_in_plugin_update_message( $pluginData, $response ) : void {
            $pluginFile = $pluginData['plugin'] ?? $this->_fix_plugin_not_in_wp_repo( $pluginData );
            if ( $pluginFile && $response->new_version == $this->_ignored_updates[$pluginFile]['ignored_version'] ) {
                $days = $this->_get_difference_in_days( $this->_ignored_updates[$pluginFile]['since'], $this->_ignored_updates[$pluginFile]['until'] );
                $isAutomatic = false;
                if ( $this->_ignored_updates[$pluginFile]['ignore_type'] == 'automatic' ) {
                    $isAutomatic = true;
                }
                echo '<br><span style="color:green;font-weight: bold">' . $this->_get_previous_ignored_text( $days, $isAutomatic ) . '</span>';
            }
        }

        private function _fix_plugin_not_in_wp_repo( $pluginData ) : string {
            foreach ( $this->_ignored_updates as $pluginFile => $data ) {
                if ( $data['slug'] == $pluginData['slug'] ) {
                    return $pluginFile;
                }
            }
            return '';
        }

        public static function uninstall_plugin() : void {
            delete_site_option( IGSPU_OPTION );
        }

        private function _can_run_plugin_code() : bool {
            if ( !is_multisite() ) {
                return true;
            }
            return false;
        }

        private function _premium_features() : void {
            ?>
            <ul>
                <li><span class="ispu-check check"></span>
                    <span class="ispu-underline"><?php 
            esc_html_e( 'Autopilot', 'ignore-single-update' );
            ?></span>: <?php 
            esc_html_e( 'Activate it to automatically delay the apparition of new updates', 'ignore-single-update' );
            ?>
                </li>
                <li><span class="ispu-check check"></span>
                    <?php 
            esc_html_e( 'Exclude plugins from Autopilot', 'ignore-single-update' );
            ?>
                </li>
                <li><span class="ispu-check check"></span>
                    <span class="ispu-underline"><?php 
            esc_html_e( 'WordFence Integration', 'ignore-single-update' );
            ?></span>: <?php 
            esc_html_e( 'Get security warnings on your installed plugins', 'ignore-single-update' );
            ?>
                </li>
                <li><span class="ispu-check check"></span>
                    <?php 
            esc_html_e( 'Without WordFence plugin', 'ignore-single-update' );
            ?>
                </li>
                <li><span class="ispu-check check"></span>
                    <?php 
            esc_html_e( 'Paid WordFence plan not required', 'ignore-single-update' );
            ?>
                </li>
                <li><span class="ispu-check check"></span>
                    <?php 
            printf( esc_html__( 'See warnings from the "%s" and "%s" pages', 'ignore-single-update' ), esc_html__( 'Plugins' ), trim( sprintf( esc_html__( 'Updates %s' ), '' ) ) );
            ?>
                </li>
                <li><span class="ispu-check check"></span>
                    <?php 
            esc_html_e( 'Automatically unignore vulnerable versions', 'ignore-single-update' );
            ?>
                </li>
                <li><span class="ispu-check check"></span>
                    <?php 
            esc_html_e( 'Prevent ignoring vulnerable versions', 'ignore-single-update' );
            ?>
                </li>
                <li><span class="ispu-check check"></span>
                    <?php 
            esc_html_e( 'Get informed in real time by email', 'ignore-single-update' );
            ?>
                </li>
            </ul>
        <?php 
        }

        private function _upgrade_to_premium_button( $type = 'badge' ) : void {
            if ( igspu_fs()->is_not_paying() ) {
                switch ( $type ) {
                    case "badge":
                        $text = __( 'Premium', 'ignore-single-update' );
                        break;
                    default:
                        if ( !igspu_fs()->is_trial_utilized() ) {
                            $text = __( 'Free Trial', 'ignore-single-update' );
                        } else {
                            $text = __( 'Upgrade To Premium', 'ignore-single-update' );
                        }
                }
                ?>
                <a href="#"
                   class="ispu-upgrade-button ispu-button ispu-upgrade"><?php 
                echo esc_html( $text );
                ?>
                </a>
            <?php 
            }
        }

        private function _get_default_settings() : array {
            load_plugin_textdomain( 'ignore-single-update', false, $this->_base_dir . '/lang' );
            $settings = [
                'permanent'                 => [
                    'type'         => 'switch',
                    'tableRefresh' => false,
                    'value'        => false,
                    'text'         => esc_html__( 'Allow to permanently ignore updates', 'ignore-single-update' ),
                    'desc'         => sprintf( esc_html__( 'Adds an "%s" link to the popup to allow you to ignore all future versions of a plugin.', 'ignore-single-update' ), esc_html__( 'Ignore all future versions', 'ignore-single-update' ) ),
                    'display'      => 'normal',
                ],
                'days'                      => [
                    'type'         => 'number',
                    'tableRefresh' => true,
                    'value'        => 3,
                    'range'        => [1, 30],
                    'unit'         => esc_html__( 'day(s)', 'ignore-single-update' ),
                    'text'         => esc_html__( 'Default number of days', 'ignore-single-update' ),
                    'desc'         => esc_html__( 'The amount of days that will be used when not entering any number in the popup.', 'ignore-single-update' ),
                    'display'      => 'normal',
                ],
                'patch_versions'            => [
                    'type'         => 'switch',
                    'tableRefresh' => false,
                    'value'        => false,
                    'text'         => esc_html__( 'Prevent ignoring patch versions', 'ignore-single-update' ),
                    'desc'         => sprintf( esc_html__( 'Will not show the "%s" link when the version is a fix (e.g. going from 4.2.5 to 4.2.6).', 'ignore-single-update' ), esc_html__( 'Ignore update', 'ignore-single-update' ) ),
                    'display'      => 'normal',
                ],
                'autopilot'                 => [
                    'type'    => 'premium',
                    'value'   => '0',
                    'text'    => esc_html__( 'Autopilot', 'ignore-single-update' ),
                    'html'    => call_user_func( [$this, '_autopilot_description'] ),
                    'display' => 'normal',
                ],
                'autopilot_ignored_plugins' => [
                    'value' => [],
                ],
                'theme'                     => [
                    'type'         => 'select',
                    'tableRefresh' => false,
                    'value'        => 'minimal',
                    'text'         => esc_html__( 'Popup theme', 'ignore-single-update' ),
                    'desc'         => esc_html__( "Changes the popups' design.", 'ignore-single-update' ),
                    'choices'      => [
                        'Bootstrap'       => 'bootstrap',
                        'Borderless'      => 'borderless',
                        'Bulma'           => 'bulma',
                        'Dark'            => 'dark',
                        'Material UI'     => 'material-ui',
                        'Minimal'         => 'minimal',
                        'Wordpress Admin' => 'wordpress-admin',
                    ],
                    'display'      => 'normal',
                ],
                'notices'                   => [
                    'type'         => 'select',
                    'tableRefresh' => false,
                    'value'        => 'all',
                    'text'         => esc_html__( 'Admin notices', 'ignore-single-update' ),
                    'desc'         => esc_html__( 'Controls which admin notices to display.', 'ignore-single-update' ),
                    'choices'      => [
                        esc_html__( 'All', 'ignore-single-update' )           => 'all',
                        esc_html__( 'Critical only', 'ignore-single-update' ) => 'critical',
                        esc_html__( 'None', 'ignore-single-update' )          => 'disabled',
                    ],
                    'display'      => 'wordfence_obsolete',
                ],
                'notice_days'               => [
                    'value' => 1,
                ],
                'ignore_popup'              => [
                    'type'         => 'switch',
                    'tableRefresh' => false,
                    'value'        => false,
                    'text'         => esc_html__( 'Remove confirmation popup', 'ignore-single-update' ),
                    'desc'         => esc_html__( 'Removes the confirmation popup when ignoring an update. The default number of days will automatically be applied.', 'ignore-single-update' ),
                    'display'      => 'normal',
                ],
                'wordfence'                 => [
                    'type'    => 'premium',
                    'value'   => false,
                    'text'    => esc_html__( 'Activate WordFence integration', 'ignore-single-update' ),
                    'desc'    => esc_html__( 'Integrate with WordFence to know if your installed plugin versions have newly discovered security flaws.', 'ignore-single-update' ),
                    'display' => 'normal',
                ],
                'wordfence_configured'      => [
                    'value' => false,
                ],
            ];
            return $settings;
        }

        private function _freemius_checkout_trigger_script() : string {
            ob_start();
            ?>
            var purchase_currency = 'usd',
            handler = FS.Checkout.configure({
            plugin_id: '13950',
            public_key: 'pk_abddc904053ff0d2213b964a13092',
            locale: '<?php 
            echo get_user_locale();
            ?>',
            });
            jQuery('.plan.enabled').on('click', function (e) {
            handler.open({
            name: 'Ignore Or Disable Plugin Update',
            plan_id: jQuery(this).data("planid"),
            <?php 
            if ( !igspu_fs()->is_trial_utilized() ) {
                ?>trial: 'free',<?php 
            }
            ?>
            image: '<?php 
            echo esc_html( $this->_path ) . '/img/igspu-icon.jpg';
            ?>',
            licenses: 1,
            currency: purchase_currency,
            });
            e.preventDefault();
            });
            <?php 
            return ob_get_clean();
        }

        public function admin_url( $url, $path ) : string {
            if ( !is_multisite() || !$this->_str_contains( $path, 'ignored-plugin-updates' ) ) {
                return $url;
            }
            return network_admin_url( $path );
        }

        public function available_licenses( $licenses ) {
            if ( !is_multisite() || !$licenses ) {
                return $licenses;
            }
            foreach ( $licenses as $key => $license ) {
                if ( $license->plan_id == '23322' ) {
                    unset($licenses[$key]);
                }
            }
            return $licenses;
        }

        public static function settings_url() : string {
            return esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates&tab=settings' ) );
        }

        public static function pricing_url() : string {
            return esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates&tab=plans' ) );
        }

        public static function init_plugin() : void {
            if ( current_user_can( 'update_plugins' ) ) {
                new self();
            }
        }

    }

}