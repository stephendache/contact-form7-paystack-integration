<?php
class CF7_Paystack_Email {

    public function __construct() {
        // ✅ Hook for saving template
        add_action('admin_init', [$this, 'save_email_template']);
    }

    // ✅ Render Email Template Settings Page (as instance method for better control)
    public function email_template_page() {
        $template = get_option('cf7_paystack_email_template', 'Hello {email}, your payment of {amount} {currency} was successful. Reference: {reference}. Thank you!');
        ?>
        <div class="wrap">
            <h1><?php _e('Customize Payment Success Email', 'cf7-paystack'); ?></h1>

            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Email template saved successfully.', 'cf7-paystack'); ?></p>
                </div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field('cf7_paystack_save_email_template', 'cf7_paystack_email_nonce'); ?>
                <textarea name="cf7_paystack_email_template" rows="10" style="width:100%; max-width:700px;"><?php echo esc_textarea($template); ?></textarea>
                <p>
                    <button type="submit" class="button button-primary"><?php _e('Save Email Template', 'cf7-paystack'); ?></button>
                </p>
            </form>

            <h3><?php _e('Available Placeholders:', 'cf7-paystack'); ?></h3>
            <ul style="line-height: 1.8;">
                <li><code>{email}</code> - <?php _e('Customer Email', 'cf7-paystack'); ?></li>
                <li><code>{amount}</code> - <?php _e('Payment Amount', 'cf7-paystack'); ?></li>
                <li><code>{currency}</code> - <?php _e('Currency (e.g., NGN)', 'cf7-paystack'); ?></li>
                <li><code>{reference}</code> - <?php _e('Transaction Reference', 'cf7-paystack'); ?></li>
            </ul>

        </div>
        <?php
        $this->admin_footer_note(); // ✅ Footer Branding
    }

    // ✅ Save Email Template Safely
    public function save_email_template() {
        if (isset($_POST['cf7_paystack_email_template']) && check_admin_referer('cf7_paystack_save_email_template', 'cf7_paystack_email_nonce')) {
            update_option('cf7_paystack_email_template', wp_kses_post($_POST['cf7_paystack_email_template']));

            // ✅ Redirect back with success message
            wp_redirect(admin_url('admin.php?page=cf7-paystack-email-template&success=1'));
            exit;
        }
    }

    // ✅ Dynamic Placeholder Replacement for Sending Emails
    public static function parse_template($template, $data) {
        $replacements = [
            '{email}'     => sanitize_email($data['email']),
            '{amount}'    => number_format_i18n($data['amount']),
            '{currency}'  => esc_html($data['currency']),
            '{reference}' => esc_html($data['reference']),
        ];

        return strtr($template, $replacements);
    }

    // ✅ Branding Footer
    public function admin_footer_note() {
        echo '<div style="margin-top: 40px; padding: 15px 0; border-top: 1px solid #ccc; text-align: center; color: #555;">
                Built with ❤️ by <a href="https://stephendache.github.io/" target="_blank">Stephen Paul</a>
              </div>';
    }
}
