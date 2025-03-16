<?php
class CF7_Paystack_Setup {

    public function __construct() {
        // ✅ Save settings handler
        add_action('admin_init', [$this, 'save_settings']);

        // ✅ Admin assets (CSS/JS)
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    // ✅ Enqueue CSS/JS only on setup page
    public function enqueue_admin_assets($hook) {
        if ($hook === 'cf7-paystack_page_cf7-paystack-setup') {
            wp_enqueue_style('cf7-paystack-admin-style', CF7_PAYSTACK_URL . 'assets/css/admin-style.css');
            wp_enqueue_script('cf7-paystack-admin-script', CF7_PAYSTACK_URL . 'assets/js/admin-script.js', ['jquery'], null, true);
        }
    }

    // ✅ Display Setup Wizard Page
    public function setup_wizard_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Contact Form 7 + Paystack Setup Wizard', 'cf7-paystack'); ?></h1>

            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Settings saved successfully.', 'cf7-paystack'); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field('cf7_paystack_setup_save', 'cf7_paystack_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><?php _e('Paystack Public Key', 'cf7-paystack'); ?></th>
                        <td><input type="text" name="paystack_public_key" value="<?php echo esc_attr(get_option('cf7_paystack_public_key')); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><?php _e('Paystack Secret Key', 'cf7-paystack'); ?></th>
                        <td><input type="text" name="paystack_secret_key" value="<?php echo esc_attr(get_option('cf7_paystack_secret_key')); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><?php _e('Currency (NGN, USD, GHS)', 'cf7-paystack'); ?></th>
                        <td><input type="text" name="paystack_currency" value="<?php echo esc_attr(get_option('cf7_paystack_currency', 'NGN')); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th><?php _e('Success Redirect URL', 'cf7-paystack'); ?></th>
                        <td><input type="url" name="paystack_success_url" value="<?php echo esc_attr(get_option('cf7_paystack_success_url')); ?>" class="regular-text" placeholder="https://example.com/success" required></td>
                    </tr>
                    <tr>
                        <th><?php _e('Failure Redirect URL', 'cf7-paystack'); ?></th>
                        <td><input type="url" name="paystack_failure_url" value="<?php echo esc_attr(get_option('cf7_paystack_failure_url')); ?>" class="regular-text" placeholder="https://example.com/failure" required></td>
                    </tr>
                </table>

                <p>
                    <button type="submit" name="cf7_paystack_save" class="button button-primary"><?php _e('Save & Continue', 'cf7-paystack'); ?></button>
                </p>
            </form>
        </div>
        <?php
        $this->admin_footer_note(); // ✅ Branding footer
    }

    // ✅ Save Setup Form Settings Securely
    public function save_settings() {
        if (isset($_POST['cf7_paystack_save']) && check_admin_referer('cf7_paystack_setup_save', 'cf7_paystack_nonce')) {
            
            // ✅ Save each option safely
            update_option('cf7_paystack_public_key', sanitize_text_field($_POST['paystack_public_key']));
            update_option('cf7_paystack_secret_key', sanitize_text_field($_POST['paystack_secret_key']));
            update_option('cf7_paystack_currency', strtoupper(sanitize_text_field($_POST['paystack_currency']))); // Store currency uppercase
            update_option('cf7_paystack_success_url', esc_url_raw($_POST['paystack_success_url']));
            update_option('cf7_paystack_failure_url', esc_url_raw($_POST['paystack_failure_url']));

            // ✅ Redirect back with success message
            wp_redirect(admin_url('admin.php?page=cf7-paystack-setup&success=1'));
            exit;
        }
    }

    // ✅ Branding Footer
    public function admin_footer_note() {
        echo '<div style="margin-top: 40px; padding: 15px 0; border-top: 1px solid #ccc; text-align: center; color: #555;">
                Built with ❤️ by <a href="https://stephendache.github.io/" target="_blank">Stephen Paul</a>
              </div>';
    }
}
