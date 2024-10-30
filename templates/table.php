<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/* @var $forceRefresh */
if ( $forceRefresh ) {
    $this->disable_plugin_updates( get_site_transient( 'update_plugins' ) );
}
$ignoredUpdates = $this->_ignored_updates;
$known_updates = $this->_updates;
$expiredUpdates = $permanentlyIgnored = $forcedIgnored = [];
foreach ( $ignoredUpdates as $pluginFile => $data ) {
    $filePath = WP_PLUGIN_DIR . '/' . $pluginFile;
    if ( !file_exists( $filePath ) || !isset( $known_updates[$pluginFile] ) ) {
        unset($ignoredUpdates[$pluginFile]);
        $this->_update_ignored_updates( $ignoredUpdates );
        continue;
    }
    $currentPluginData = get_plugin_data( $filePath );
    if ( $data['ignored_version'] == 'Any' ) {
        $permanentlyIgnored[$pluginFile] = $data;
        $permanentlyIgnored[$pluginFile]['current-version'] = $currentPluginData['Version'];
        unset($ignoredUpdates[$pluginFile]);
    } elseif ( version_compare( $currentPluginData['Version'], $data['ignored_version'], '>=' ) ) {
        $expiredUpdates[$pluginFile] = $data;
        $expiredUpdates[$pluginFile]['current-version'] = $currentPluginData['Version'];
        $expiredUpdates[$pluginFile]['reason'] = esc_html__( 'Plugin updated', 'ignore-single-update' );
        unset($ignoredUpdates[$pluginFile]);
    } elseif ( version_compare( $data['latest_known_version']['version'], $data['ignored_version'], '>' ) ) {
        $expiredUpdates[$pluginFile] = $data;
        $expiredUpdates[$pluginFile]['current-version'] = $currentPluginData['Version'];
        $expiredUpdates[$pluginFile]['reason'] = esc_html__( 'New version released', 'ignore-single-update' );
        unset($ignoredUpdates[$pluginFile]);
    } elseif ( $this->_date_limit_reached( $pluginFile ) ) {
        if ( $data['ignore_type'] != 'automatic' ) {
            $expiredUpdates[$pluginFile] = $data;
            $expiredUpdates[$pluginFile]['current-version'] = $currentPluginData['Version'];
            $expiredUpdates[$pluginFile]['reason'] = esc_html__( 'Days elapsed', 'ignore-single-update' );
        }
        unset($ignoredUpdates[$pluginFile]);
    } else {
        $ignoredUpdates[$pluginFile]['current-version'] = $currentPluginData['Version'];
    }
    $this->_ignored_updates = $ignoredUpdates;
}
$totalCount = count( $forcedIgnored ) + count( $ignoredUpdates ) + count( $permanentlyIgnored );
if ( !$ignoredUpdates && !$expiredUpdates && !$permanentlyIgnored && !$forcedIgnored ) {
    ?>
    <div class="ispu-info ispu-refreshable">
        <div class="nothing-ignored"><?php 
    esc_html_e( 'You currently do not have any active ignored plugin updates.', 'ignore-single-update' );
    ?></div>
        <?php 
    $this->_counter_update_script( $totalCount );
    ?>
    </div>
<?php 
} else {
    ?>
    <table class="ignored-plugins-table ispu-refreshable">
        <tbody class="refreshingtable"></tbody>
        <?php 
    if ( $permanentlyIgnored ) {
        $this->_render_table_body( $permanentlyIgnored, 'permanent' );
    }
    if ( $ignoredUpdates || $forcedIgnored ) {
        global $wp_filter;
        $knownCallbacks = '';
        // We need to temporarily deactivate plugin custom plugins_api callbacks, and rely only on WP REPO for "View details"
        // Calling plugins_api in render_table_body triggers fatal errors when custom callbacks fail (cloudflare errors/403, etc.)
        if ( apply_filters( 'igspu_wp_filter_ignore', true ) && isset( $wp_filter['plugins_api']->callbacks ) ) {
            $knownCallbacks = $wp_filter['plugins_api']->callbacks;
            $wp_filter['plugins_api']->callbacks = [];
        }
        if ( $ignoredUpdates ) {
            $this->_render_table_body( $ignoredUpdates, 'ignored', $known_updates );
        }
        if ( $forcedIgnored ) {
            $this->_render_table_body( $forcedIgnored, 'forced' );
        }
        //Reactivate the callbacks
        if ( $knownCallbacks ) {
            $wp_filter['plugins_api']->callbacks = $knownCallbacks;
        }
    }
    if ( $expiredUpdates ) {
        $this->_render_table_body( $expiredUpdates, 'expired' );
    }
    ?>
        <?php 
    $this->_counter_update_script( $totalCount );
    ?>
    </table>
<?php 
}