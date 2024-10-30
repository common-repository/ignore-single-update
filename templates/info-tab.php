<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="ispu-info">
    <section>
        <h2><?php 
esc_html_e( 'What Does This Plugin Do?', 'ignore-single-update' );
?></h2>
        <div class="ispu-info-content what-it-does">
            <p><?php 
esc_html_e( 'With this plugin, you can:', 'ignore-single-update' );
?></p>
            <ul>
                <li>
                    <?php 
esc_html_e( 'Ignore the latest version of the plugin of your choice, for the duration of your choice.', 'ignore-single-update' );
?>
                </li>
                <li>
                    <?php 
esc_html_e( 'Delay seeing potentially bugged plugin updates, by auto-ignoring new releases for a set number of days.', 'ignore-single-update' );
?>
                    <?php 
$this->_upgrade_to_premium_button();
?>
                </li>
                <li>
                    <?php 
esc_html_e( 'Easily see what updates are currently ignored.', 'ignore-single-update' );
?>
                </li>
                <li>
                    <?php 
esc_html_e( 'Completely ignore future updates for a given plugin.', 'ignore-single-update' );
?>
                </li>
                <li>
                    <?php 
esc_html_e( 'Integrate with WordFence to know about your plugins security issues.', 'ignore-single-update' );
?>
                    <?php 
$this->_upgrade_to_premium_button();
?>
                </li>
            </ul>
        </div>
    </section>
    <section>
        <h2><?php 
esc_html_e( 'How To Use', 'ignore-single-update' );
?></h2>
        <div class="ispu-info-content how-to-use">
            <ul>
                <li><?php 
esc_html_e( 'The plugin works out of the box, without needing to configure extra settings.', 'ignore-single-update' );
?></li>
                <li><?php 
printf(
    esc_html__( 'It adds an "%s" link in the "%s" and "%s" WP screens.', 'ignore-single-update' ),
    esc_html__( 'Ignore update', 'ignore-single-update' ),
    esc_html__( 'Plugins' ),
    trim( sprintf( esc_html__( 'Updates %s' ), '' ) )
);
?></li>
                <li><?php 
esc_html_e( 'Clicking on it prompts for a number of days for which the update notice should not be displayed, for this specific plugin.', 'ignore-single-update' );
?></li>
                <li><?php 
esc_html_e( 'Entering any number of days will postpone the re-apparition of the notice.', 'ignore-single-update' );
?></li>
                <li><?php 
esc_html_e( 'Entering 0 will postpone the re-apparition of the notice until the next plugin version.', 'ignore-single-update' );
?></li>
                <li><?php 
printf( esc_html__( 'Ignored updates can be unignored at any time by going in the "%s"->"%s" WP menu.', 'ignore-single-update' ), esc_html__( 'Plugins' ), esc_html__( 'Ignored Updates', 'ignore-single-update' ) );
?></li>
            </ul>
        </div>
    </section>
    <?php 
if ( current_user_can( 'administrator' ) ) {
    ?>
        <section>
            <h2><?php 
    esc_html_e( 'Additional Settings', 'ignore-single-update' );
    ?></h2>
            <?php 
    $additionalSettings = ['add_filter("igspu_dismissible_critical_notice","__return_true");', 'add_filter("igspu_show_notice_days_setting","__return_true");'];
    if ( !is_multisite() ) {
        $additionalSettings[] = 'add_filter("igspu_restrict_settings_to_admin","__return_true");';
    }
    ?>
            <div class="ispu-info-content additional-settings">
                <?php 
    if ( count( $additionalSettings ) ) {
        ?>
                    <p><?php 
        printf( _n(
            'There is %s additional setting, only accessible via WP filter:',
            'There are %s additional settings, only accessible via WP filters:',
            count( $additionalSettings ),
            'ignore-single-update'
        ), count( $additionalSettings ) );
        ?></p>
                    <ul>
                        <?php 
        foreach ( $additionalSettings as $additionalSetting ) {
            ?>
                            <li>
                                <code>
                                    <?php 
            echo $additionalSetting;
            ?>
                                </code>
                            </li>
                        <?php 
        }
        ?>
                    </ul>
                <?php 
    }
    ?>
                <?php 
    ?>
            </div>
        </section>
    <?php 
}
?>
    <?php 
?>
    <section>
        <h2><?php 
esc_html_e( 'Misc', 'ignore-single-update' );
?></h2>
        <div class="ispu-info-content misc">
            <p><?php 
echo esc_html__( 'Plugin version:', 'ignore-single-update' ) . ' ' . IGSPU_VERSION;
?></p>
            <p><?php 
echo esc_html__( 'Plan Type:', 'ignore-single-update' );
$planType = ' Basic&nbsp;&nbsp;<a href="#" class="ispu-upgrade-button ispu-upgrade ispu-button ispu-option-wordfence">' . esc_html__( 'Upgrade To Premium', 'ignore-single-update' ) . '</a>';
if ( igspu_fs()->is_trial() ) {
    $planType .= ' (' . esc_html__( 'Free Trial', 'ignore-single-update' ) . ')';
}
echo $planType;
?>
            <p><?php 
esc_html_e( 'Thank you for using my plugin', 'ignore-single-update' );
?> :)</p>
        </div>
    </section>
</div>