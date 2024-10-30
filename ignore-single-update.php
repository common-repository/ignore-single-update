<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Plugin Name: Ignore Or Disable Plugin Update
 * Description: Allows to ignore a single plugin update for a certain number of days, or until its next version.
 * Version:     1.2.1
 * Author:      JFG Media
 * Author URI:  https://jfgmedia.com
 * Text Domain: ignore-single-update
 * Domain Path: /lang
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 */
if ( function_exists( 'igspu_fs' ) ) {
    igspu_fs()->set_basename( false, __FILE__ );
} else {
    define( 'IGSPU_OPTION', 'igspu_ignored_plugin_updates' );
    define( 'IGSPU_PLUGIN_FILE', __FILE__ );
    if ( !function_exists( 'igspu_fs' ) ) {
        // Create a helper function for easy SDK access.
        function igspu_fs() {
            global $igspu_fs;
            if ( !isset( $igspu_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_13950_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_13950_MULTISITE', true );
                }
                $trial = [
                    'days'               => 30,
                    'is_require_payment' => false,
                ];
                if ( is_multisite() ) {
                    $trial = false;
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/sdk/start.php';
                $igspu_fs = fs_dynamic_init( [
                    'id'             => '13950',
                    'slug'           => 'ignore-single-update',
                    'premium_slug'   => 'ignore-single-update-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_abddc904053ff0d2213b964a13092',
                    'is_premium'     => false,
                    'premium_suffix' => 'Premium',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'navigation'     => 'tabs',
                    'anonymous_mode' => true,
                    'trial'          => $trial,
                    'menu'           => [
                        'slug'           => 'ignored-plugin-updates',
                        'override_exact' => true,
                        'contact'        => true,
                        'support'        => true,
                        'pricing'        => false,
                        'network'        => true,
                        'account'        => true,
                        'parent'         => [
                            'slug' => 'plugins.php',
                        ],
                    ],
                    'is_live'        => true,
                ] );
            }
            return $igspu_fs;
        }

        // Init Freemius.
        igspu_fs();
        // Signal that SDK was initiated.
        do_action( 'igspu_fs_loaded' );
        igspu_fs()->add_filter( 'connect_url', ['IGSPU\\IGSPU', 'settings_url'] );
        igspu_fs()->add_filter( 'after_skip_url', ['IGSPU\\IGSPU', 'settings_url'] );
        igspu_fs()->add_filter( 'after_connect_url', ['IGSPU\\IGSPU', 'settings_url'] );
        igspu_fs()->add_filter( 'after_pending_connect_url', ['IGSPU\\IGSPU', 'settings_url'] );
        igspu_fs()->add_filter( 'hide_freemius_powered_by', '__return_true' );
        igspu_fs()->add_filter( 'pricing_url', ['IGSPU\\IGSPU', 'pricing_url'] );
        igspu_fs()->add_filter( 'enable_per_site_activation', '__return_false' );
        igspu_fs()->add_filter( 'show_delegation_option', '__return_false' );
    }
    require_once __DIR__ . '/classes/class-igspu.php';
    add_action( 'plugins_loaded', ['IGSPU\\IGSPU', 'init_plugin'] );
    register_uninstall_hook( __FILE__, ['IGSPU\\IGSPU', 'uninstall_plugin'] );
}