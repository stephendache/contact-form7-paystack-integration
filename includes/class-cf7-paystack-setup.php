<?php
class CF7_Paystack_Setup {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_setup_menu']);
        add_action('admin_init', [$this, 'save_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    // Register Setup Wizard Menu
    public function register_setup_menu() {
        add_menu_page(
            __('CF7 Paystack Setup', 'cf7-paystack'),
            __('CF7 Paystack Setup', 'cf7-paystack'),
            'manage_options',
            'cf7-paystack-setup',
            [$this, 'setup_wizard_page'],
            'dashicons-admin-generic',
            56
        );
    }

    // Enqueue CSS/JS for Setup Wizard
    public function enqueue_admin_assets($hook) {
        if ($hook === 'toplevel_page_cf7-paystack-setup') {
            wp_enqueue_style('cf7-paystack-admin-style', CF7_PAYSTACK_URL . 'assets/css/admin-style.css');
            wp_enqueue_script('cf7-paystack-admin-script', CF7_PAYSTACK_URL . 'assets/js/admin-script.js', ['jquery'], null, true);
        }
    }

    // Display Setup Wizard Form
    public function setup_wizard_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Contact Form 7 + Paystack Setup Wizard', 'cf7-paystack'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('cf7_paystack_setup_save', 'cf7_paystack_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><?php _e('Paystack Public Key', 'cf7-paystack'); ?></th>
                        <td><input type="text" name="paystack_public_key" value="<?php echo esc_attr(get_option('cf7_paystack_public_key')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php _e('Paystack Secret Key', 'cf7-paystack'); ?></th>
                        <td><input type="text" name="paystack_secret_key" value="<?php echo esc_attr(get_option('cf7_paystack_secret_key')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php _e('Currency (NGN, USD, GHS)', 'cf7-paystack'); ?></th>
                        <td><input type="text" name="paystack_currency" value="<?php echo esc_attr(get_option('cf7_paystack_currency', 'NGN')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php _e('Success Redirect URL', 'cf7-paystack'); ?></th>
                        <td><input type="url" name="paystack_success_url" value="<?php echo esc_attr(get_option('cf7_paystack_success_url')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php _e('Failure Redirect URL', 'cf7-paystack'); ?></th>
                        <td><input type="url" name="paystack_failure_url" value="<?php echo esc_attr(get_option('cf7_paystack_failure_url')); ?>" class="regular-text"></td>
                    </tr>
                </table>
                <p><input type="submit" name="cf7_paystack_save" class="button-primary" value="<?php _e('Save & Continue', 'cf7-paystack'); ?>"></p>
            </form>
        </div>
        <?php
    }

    // Save Setup Form Settings
    public function save_settings() {
        if (isset($_POST['cf7_paystack_save']) && check_admin_referer('cf7_paystack_setup_save', 'cf7_paystack_nonce')) {
            update_option('cf7_paystack_public_key', sanitize_text_field($_POST['paystack_public_key']));
            update_option('cf7_paystack_secret_key', sanitize_text_field($_POST['paystack_secret_key']));
            update_option('cf7_paystack_currency', sanitize_text_field($_POST['paystack_currency']));
            update_option('cf7_paystack_success_url', esc_url_raw($_POST['paystack_success_url']));
            update_option('cf7_paystack_failure_url', esc_url_raw($_POST['paystack_failure_url']));
            wp_redirect(admin_url('admin.php?page=cf7-paystack-setup&success=1'));
            exit;
        }
    }
}
