<?php if (!defined('ABSPATH')) exit; ?>
<h2><?php esc_html_e('Frequently Asked Questions', 'ignore-single-update'); ?></h2>
<div class="ispu-plan-section faq">
    <?php if ($presaleFaq) { ?>
        <details>
            <summary><?php esc_html_e('Is there a setup fee?', 'ignore-single-update'); ?></summary>
            <p>
                <?php esc_html_e('No. There are no setup fees on any of our plans.', 'ignore-single-update'); ?>
            </p>
        </details>
    <?php } ?>
    <details>
        <summary><?php esc_html_e('Can I cancel my account at any time?', 'ignore-single-update'); ?></summary>
        <p>
            <?php printf(esc_html__("Yes, if you ever decide that %s isn't the best plugin for your business, simply cancel your account from your Account panel.", 'ignore-single-update'),'Ignore Or Disable Plugin Update'); ?>
        </p>
    </details>
    <details>
        <summary><?php esc_html_e("What's the time span for your contracts?", 'ignore-single-update'); ?></summary>
        <p>
            <?php esc_html_e('All plans are year-to-year.', 'ignore-single-update'); ?>
        </p>
    </details>
    <details>
        <summary><?php esc_html_e('Can I change my plan later on?', 'ignore-single-update'); ?></summary>
        <p>
            <?php esc_html_e('Absolutely! You can upgrade or downgrade your plan at any time.', 'ignore-single-update'); ?>
        </p>
    </details>
    <?php if ($presaleFaq) { ?>
        <details>
            <summary><?php esc_html_e('What payment methods are accepted?', 'ignore-single-update'); ?></summary>
            <p>
                <?php esc_html_e('We accept all major credit cards including Visa, Mastercard, American Express, as well as PayPal payments.', 'ignore-single-update'); ?>
            </p>
        </details>
    <?php } ?>
    <details>
        <summary><?php esc_html_e('Do you offer refunds?', 'ignore-single-update'); ?></summary>
        <p>
            <?php esc_html_e('Yes we do! We stand behind the quality of our product and will refund 100% of your money if you experience an issue that makes the plugin unusable and we are unable to resolve it.', 'ignore-single-update'); ?>
            <br><a href="#"
                   class="show-refund-policy"><?php esc_html_e('Click here to display our refund policy.', 'ignore-single-update'); ?></a>
        </p>
        <div id="refund-policy">
            <div class="refund-heading"><?php esc_html_e('Refund Policy', 'ignore-single-update'); ?></div>
            <div class="refund-content">
                <p><?php esc_html_e('We stand behind our plugin’s quality and your satisfaction with it is important to us.', 'ignore-single-update'); ?></p>
                <p><?php esc_html_e('If you experience problems with the plugin, we will be happy to provide a full refund within 14 days of the original upgrade date.', 'ignore-single-update'); ?></p>
                <p><?php echo wp_kses(__('Refunds will be offered at our sole discretion and must meet all the following conditions <strong>fully</strong>:', 'ignore-single-update'), ['strong' => []]); ?></p>
                <ul>
                    <li><?php esc_html_e('You are within the first 14 days of the purchase of the plugin.', 'ignore-single-update'); ?></li>
                    <li><?php esc_html_e('Your issue(s) derives from not being able to install the plugin properly or get the plugin to perform its basic functions.', 'ignore-single-update'); ?></li>
                    <li><?php printf(esc_html__('You have attempted to resolve your issue(s) with our support team by opening a support ticket through the "%s" tab in the plugin’s admin settings.', 'ignore-single-update'), esc_html__("Contact Us", 'freemius')); ?></li>
                    <li><?php esc_html_e('No refunds will be granted after the first 14 days of the original purchase whatsoever.', 'ignore-single-update'); ?></li>
                    <li><?php esc_html_e('Refunds will not be granted for missing feature(s). If you are not sure we support a specific feature, please contact us first.', 'ignore-single-update'); ?></li>
                    <li><?php esc_html_e('Issues caused by conflicts with 3rd party plugins, themes or other software will not provide grounds for a refund.', 'ignore-single-update'); ?></li>
                    <li><?php esc_html_e('Refunds will not be granted if you simply decide not to use the plugin.', 'ignore-single-update'); ?></li>
                </ul>
                <p><?php esc_html_e('By upgrading, you agree to this refund policy and relinquish any rights to subject it to any questions, judgment or legal actions.', 'ignore-single-update'); ?></p>
                <?php if (!$presaleFaq) { ?>
                    <p><?php printf(wp_kses(__('To submit a refund request, please open a <a href="%s">refund support ticket</a>.', 'ignore-single-update'), ['a' => ['href' => []]]), igspu_fs()->contact_url('refund')); ?></p>
                <?php } ?>
            </div>
        </div>
    </details>
    <details>
        <summary><?php esc_html_e('Do I need multiple licenses for Multisite?', 'ignore-single-update'); ?></summary>
        <p>
            <?php esc_html_e('No! You only need one single Business license for the plugin to work on your entire multisite network.', 'ignore-single-update'); ?>
        </p>
    </details>
    <details>
        <summary><?php esc_html_e('Do I get updates for the premium plugin?', 'ignore-single-update'); ?></summary>
        <p>
            <?php esc_html_e('Yes! Automatic updates to our premium plugin are available free of charge as long as you stay our paying customer.', 'ignore-single-update'); ?>
        </p>
    </details>
    <details>
        <summary><?php esc_html_e('Do you offer support if I need help?', 'ignore-single-update'); ?></summary>
        <p>
            <?php esc_html_e("Yes! Top-notch customer support for our paid customers is key for a quality product, so we'll do our very best to resolve any issues you encounter via our support page.", 'ignore-single-update'); ?>
        </p>
    </details>
    <?php if ($presaleFaq) { ?>
        <details>
            <summary><?php esc_html_e('I have other pre-sale questions, can you help?', 'ignore-single-update'); ?></summary>
            <p>
                <?php printf(wp_kses(__('Yes! You can ask us any question by using our <a href="%s">contact form</a>.', 'ignore-single-update'), ['a' => ['href' => []]]), igspu_fs()->contact_url('pre_sale_question')); ?>
            </p>
        </details>
    <?php } ?>
</div>