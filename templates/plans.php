<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="ispu-plans">
    <section>
        <?php 
if ( !$this->_is_on ) {
    ?>
            <div class="ispu-multisite-not-supported">
                <?php 
    esc_html_e( 'Your current plan does not support Multisite. Please Upgrade to the Business plan to use the plugin on a Multisite install.', 'ignore-single-update' );
    ?>
            </div>
        <?php 
} else {
    ?>
            <h2><?php 
    esc_html_e( 'Never Ignore Critical Updates', 'ignore-single-update' );
    ?></h2>
        <?php 
}
?>
        <div class="ispu-currency-switch">
            <input id="toggle-usd" class="toggle toggle-left" name="currency-toggle" value="usd" type="radio" checked>
            <label for="toggle-usd" class="currency-btn">USD</label>
            <input id="toggle-eur" class="toggle toggle-right" name="currency-toggle" value="eur" type="radio">
            <label for="toggle-eur" class="currency-btn">EUR</label>
        </div>
        <div class="ispu-plan-section ispu-pricing">
            <div class="plan<?php 
if ( $forcedPlan ) {
    ?> disabled<?php 
}
?>">
                <h3>Basic</h3>
                <div class="price"><?php 
esc_html_e( 'FREE', 'ignore-single-update' );
?></div>
                <ul class="features">
                    <li><span class="ispu-check cross"></span> <?php 
esc_html_e( 'No Autopilot', 'ignore-single-update' );
?>
                    </li>
                    <li><span class="ispu-check cross"></span> <?php 
esc_html_e( 'No WordFence Integration', 'ignore-single-update' );
?>
                    </li>
                    <li><span class="ispu-check cross"></span> <?php 
esc_html_e( 'No Email Support', 'ignore-single-update' );
?></li>
                    <li>
                        <span class="ispu-check cross"></span> <?php 
esc_html_e( 'No Multisite Compatibility', 'ignore-single-update' );
?>
                    </li>
                </ul>
            </div>
            <div class="plan<?php 
if ( !$forcedPlan || $forcedPlan == 'supporter' ) {
    ?> enabled popular" data-planid="23322<?php 
} else {
    ?> disabled<?php 
}
?>">
                <?php 
if ( !$forcedPlan ) {
    ?><span
                        class="recommended"><?php 
    esc_html_e( 'Recommended', 'ignore-single-update' );
    ?></span> <?php 
}
?>
                <h3>Supporter</h3>
                <div class="price">
                    <span class="currency-symbol">$</span>10<span class="per-year">/<?php 
esc_html_e( 'year', 'ignore-single-update' );
?></span>
                </div>
                <ul class="features">
                    <li><span class="ispu-check check"></span> <?php 
esc_html_e( 'Autopilot', 'ignore-single-update' );
?></li>
                    <li><span class="ispu-check check"></span> <?php 
esc_html_e( 'WordFence Integration', 'ignore-single-update' );
?>
                    </li>
                    <li><span class="ispu-check check"></span> <?php 
esc_html_e( 'Email Support', 'ignore-single-update' );
?></li>
                    <li>
                        <span class="ispu-check cross"></span> <?php 
esc_html_e( 'No Multisite Compatibility', 'ignore-single-update' );
?>
                    </li>
                </ul>
                <?php 
if ( !$forcedPlan || $forcedPlan == 'supporter' ) {
    ?>
                    <button class="ispu-purchase"><?php 
    if ( igspu_fs()->is_trial_utilized() ) {
        esc_html_e( 'Buy Now', 'ignore-single-update' );
    } else {
        esc_html_e( 'Try Free For 30 Days', 'ignore-single-update' );
    }
    ?></button>
                <?php 
    if ( igspu_fs()->is_trial_utilized() ) {
        ?>
                        <img src="<?php 
        echo esc_attr( $this->_path );
        ?>/img/cards.png" width="171" height="15">
                    <?php 
    } else {
        esc_html_e( 'No credit card required', 'ignore-single-update' );
    }
}
?>
            </div>
            <div class="plan enabled<?php 
if ( $forcedPlan == 'business' ) {
    ?> popular<?php 
}
?>" data-planid="23442">
                <?php 
if ( $forcedPlan == 'business' ) {
    ?>
                    <span class="recommended"><?php 
    esc_html_e( 'Recommended', 'ignore-single-update' );
    ?></span> <?php 
}
?>
                <h3>Business</h3>
                <div class="price"><span class="currency-symbol">$</span>50<span class="per-year">/<?php 
esc_html_e( 'year', 'ignore-single-update' );
?></span>
                </div>
                <ul class="features">
                    <li><span class="ispu-check check"></span> <?php 
esc_html_e( 'Autopilot', 'ignore-single-update' );
?></li>
                    <li><span class="ispu-check check"></span> <?php 
esc_html_e( 'WordFence Integration', 'ignore-single-update' );
?>
                    </li>
                    <li><span class="ispu-check check"></span> <?php 
esc_html_e( 'Priority Email Support', 'ignore-single-update' );
?>
                    </li>
                    <li><span class="ispu-check check"></span> <?php 
esc_html_e( 'Multisite Compatibility', 'ignore-single-update' );
?>
                    </li>
                </ul>
                <button class="ispu-purchase"><?php 
esc_html_e( 'Buy Now', 'ignore-single-update' );
?></button>
                <img src="<?php 
echo esc_attr( $this->_path );
?>/img/cards.png" width="171" height="15">
            </div>
            <?php 
?>
        </div>
    </section>
    <section>
        <h2><?php 
esc_html_e( 'Screenshots', 'ignore-single-update' );
?></span></h2>
        <div class="ispu-screenshots">
            <div class="ispu-right">
                <a href="<?php 
echo esc_attr( $this->_path );
?>/img/wordfence-plugin-page-warning.jpg" class="swal-image"
                   data-width="1209" data-height="668" title="<?php 
esc_html_e( 'Plugins page', 'ignore-single-update' );
?>"><img
                            alt="<?php 
esc_html_e( 'Plugins page', 'ignore-single-update' );
?>"
                            src="<?php 
echo esc_attr( $this->_path );
?>/img/wordfence-plugin-page-warning.jpg"></a>
                <a href="<?php 
echo esc_attr( $this->_path );
?>/img/wordfence-core-page-warning.jpg" class="swal-image"
                   data-width="1121" data-height="695" title="<?php 
esc_html_e( 'Updates page', 'ignore-single-update' );
?>"><img
                            alt="<?php 
esc_html_e( 'Updates page', 'ignore-single-update' );
?>"
                            src="<?php 
echo esc_attr( $this->_path );
?>/img/wordfence-core-page-warning.jpg"></a>
                <a href="<?php 
echo esc_attr( $this->_path );
?>/img/wordfence-mail-warning.jpg" class="swal-image"
                   data-width="890" data-height="466" title="Email"><img alt="Email"
                                                                         src="<?php 
echo esc_attr( $this->_path );
?>/img/wordfence-mail-warning.jpg"></a>
            </div>
            <div class="ispu-left">
                <?php 
$this->_premium_features();
?>
            </div>
        </div>
    </section>
    <section>
        <?php 
$presaleFaq = true;
require_once __DIR__ . '/faq.php';
wp_enqueue_script( 'freemius-checkout', 'https://checkout.freemius.com/checkout.min.js' );
wp_add_inline_script( 'freemius-checkout', $this->_freemius_checkout_trigger_script() );
?>
    </section>
</div>