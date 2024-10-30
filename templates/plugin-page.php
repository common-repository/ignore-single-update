<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( is_multisite() && !$this->_is_on ) {
    ?>
    <div class="wrap">
        <h1><?php 
    esc_html_e( 'Ignored Plugin Updates', 'ignore-single-update' );
    ?></h1>
        <div class="ispu-tabs-wrapper">
            <nav class="nav-tab-wrapper ispu-settings-nav">
                <a href="<?php 
    echo esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates&tab=settings' ) );
    ?>"
                   id="ispu-switch-2" data-tab="2"
                   class="nav-tab nav-tab-active"><?php 
    esc_html_e( 'Settings' );
    ?></a>
            </nav>
            <div id="ispu-tab-2" class="ispu-tab">
                <?php 
    $this->_render_pricing( 'business' );
    ?>
            </div>
        </div>
    </div>
    <?php 
    return;
}
$tab = $_GET['tab'] ?? '';
if ( !in_array( $tab, ['settings', 'info', 'plans'] ) ) {
    $tab = '';
}
?>

<div class="wrap">
    <script> let newTotalCount, knownTotalCount; </script>
    <h1><?php 
esc_html_e( 'Ignored Plugin Updates', 'ignore-single-update' );
?></h1>
    <div class="ispu-tabs-wrapper">
        <?php 
?>
        <nav class="nav-tab-wrapper ispu-settings-nav">
            <style scoped>
                .ispu-go-premium {
                    background: #2196F3;
                    color: white;
                }

                .ispu-go-premium:hover, .ispu-go-premium:focus {
                    background: #2196F3;
                    color: white;
                }
            </style>
            <a href="<?php 
echo esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates' ) );
?>" id="ispu-switch-1"
               data-tab="1"
               class="nav-tab<?php 
if ( !$tab ) {
    echo ' nav-tab-active';
}
?>"><?php 
esc_html_e( 'Ignored Updates', 'ignore-single-update' );
?></a>
            <?php 
if ( $this->_user_can_access_settings() ) {
    ?>
                <a href="<?php 
    echo esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates&tab=settings' ) );
    ?>"
                   id="ispu-switch-2" data-tab="2"
                   class="nav-tab<?php 
    if ( $tab == 'settings' ) {
        echo ' nav-tab-active';
    }
    ?>"><?php 
    esc_html_e( 'Settings' );
    ?></a>
            <?php 
}
?>
            <a href="<?php 
echo esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates&tab=info' ) );
?>"
               id="ispu-switch-3" data-tab="3"
               class="nav-tab<?php 
if ( $tab == 'info' ) {
    echo ' nav-tab-active';
}
?>"><?php 
esc_html_e( 'Info', 'ignore-single-update' );
?></a>
            <?php 
if ( igspu_fs()->is_not_paying() ) {
    ?>
                <a href="<?php 
    echo esc_url( admin_url( 'plugins.php?page=ignored-plugin-updates&tab=plans' ) );
    ?>"
                   id="ispu-switch-4" data-tab="4"
                   class="nav-tab<?php 
    if ( $tab == 'plans' ) {
        echo ' nav-tab-active';
    }
    ?> ispu-go-premium"><?php 
    esc_html_e( 'Premium', 'ignore-single-update' );
    ?></a>
            <?php 
}
?>
        </nav>
        <div id="ispu-tab-1" class="ispu-tab<?php 
if ( $tab ) {
    echo ' hidden';
}
?>">
            <?php 
echo $this->render_table();
?>
        </div>
        <div id="ispu-tab-2" class="ispu-tab<?php 
if ( $tab != 'settings' ) {
    echo ' hidden';
}
?>">
            <?php 
$this->_render_settings();
?>
        </div>
        <div id="ispu-tab-3" class="ispu-tab<?php 
if ( $tab != 'info' ) {
    echo ' hidden';
}
?>">
            <?php 
$this->_render_info();
?>
        </div>
        <?php 
if ( igspu_fs()->is_not_paying() ) {
    ?>
            <div id="ispu-tab-4" class="ispu-tab<?php 
    if ( $tab != 'plans' ) {
        echo ' hidden';
    }
    ?>">
                <?php 
    $this->_render_pricing();
    ?>
            </div>
        <?php 
}
?>
    </div>
    <?php 
if ( igspu_fs()->is_not_paying() && !igspu_fs()->is_trial() ) {
    ?>
        <div class="ispu-sidebar-wrapper">
            <div class="ispu-sidebar upgrade-box"><p>
                    <?php 
    echo wp_kses( __( 'There is <span class="ispu-underline">a PREMIUM version</span>. Here are its <span class="ispu-underline">main features</span>:', 'ignore-single-update' ), [
        'span' => [
            'class' => [],
        ],
    ] );
    ?>
                </p>
                <?php 
    $this->_premium_features();
    ?>
                <p class="text-center<?php 
    if ( isset( $_GET['tab'] ) && $_GET['tab'] == 'plans' ) {
        echo ' ispu-hidden';
    }
    ?>"><a href="#" target="_blank" class="button ispu-upgrade">
                        <?php 
    if ( igspu_fs()->is_trial_utilized() ) {
        esc_html_e( 'Get Premium', 'ignore-single-update' );
    } else {
        esc_html_e( 'Try Free For 30 Days', 'ignore-single-update' );
        echo '<br><span style="font-size:9px">';
        esc_html_e( 'No credit card required', 'ignore-single-update' );
        echo '</span>';
    }
    ?>
                    </a></p>
            </div>
            <div class="ispu-sidebar rate-box"><p>
                    <?php 
    printf( wp_kses( __( 'Please <a href="%s" target="_blank">rate the plugin ★★★★★</a> to help keep its free version up-to-date & maintained. Thank you!', 'ignore-single-update' ), [
        'a' => [
            'href'   => [],
            'target' => [],
        ],
    ] ), 'https://wordpress.org/support/plugin/ignore-single-update/reviews/?filter=5#new-post' );
    ?>
                </p></div>
        </div>
    <?php 
}
?>
</div>