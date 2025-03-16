<?php

/**
 * Plugin Name: Contact Form 7 Paystack Integration
 * Plugin URI: https://stephendache.github.io/
 * Description: Integrate Paystack payment gateway with Contact Form 7 and handle email sending after successful payment. Developed and maintained by Stephen Paul.
 * Version: 1.0
 * Author: Stephen Paul
 * Author URI: https://stephendache.github.io/
 * Text Domain: cf7-paystack
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// =======================
// Define Paths
// =======================
define('CF7_PAYSTACK_PATH', plugin_dir_path(__FILE__));
define('CF7_PAYSTACK_URL', plugin_dir_url(__FILE__));

// =======================
// Load Plugin Text Domain (Translation Ready)
// =======================
add_action('plugins_loaded', function () {
    load_plugin_textdomain('cf7-paystack', false, dirname(plugin_basename(__FILE__)) . '/languages/');
});

// =======================
// Include Required Classes
// =======================
require_once CF7_PAYSTACK_PATH . 'includes/class-cf7-paystack-admin.php';
require_once CF7_PAYSTACK_PATH . 'includes/class-cf7-paystack-setup.php';
require_once CF7_PAYSTACK_PATH . 'includes/class-cf7-paystack-webhook.php';
require_once CF7_PAYSTACK_PATH . 'includes/class-cf7-paystack-api.php';
require_once CF7_PAYSTACK_PATH . 'includes/class-cf7-paystack-email.php';

// =======================
// Initialize Core Classes as Global Instances
// =======================
global $cf7_paystack_admin, $cf7_paystack_setup, $cf7_paystack_webhook, $cf7_paystack_api, $cf7_paystack_email;
$cf7_paystack_admin = new CF7_Paystack_Admin();   // Transactions
$cf7_paystack_setup = new CF7_Paystack_Setup();   // Settings
$cf7_paystack_webhook = new CF7_Paystack_Webhook(); // Handle Paystack webhooks
$cf7_paystack_api = new CF7_Paystack_API();     // Handle AJAX/API
$cf7_paystack_email = new CF7_Paystack_Email();  // ✅ Handle email template customization

// =======================
// Create Transaction Table on Activation
// =======================
register_activation_hook(__FILE__, 'cf7_paystack_create_transactions_table');
function cf7_paystack_create_transactions_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cf7_paystack_transactions';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        reference varchar(200) NOT NULL,
        email varchar(200) NOT NULL,
        amount bigint(20) NOT NULL,
        form_data longtext NOT NULL,
        status varchar(100) DEFAULT 'pending' NOT NULL,
        currency varchar(10) DEFAULT 'NGN',
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// =======================
// Enqueue Frontend Scripts (Properly)
// =======================
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('cf7-paystack-redirect', CF7_PAYSTACK_URL . 'assets/js/cf7-paystack-redirect.js', ['jquery'], null, true);
    wp_enqueue_script('cf7-paystack-ajax', CF7_PAYSTACK_URL . 'assets/js/cf7-paystack-ajax.js', ['jquery'], null, true);

    // Localize API URL
    wp_localize_script('cf7-paystack-ajax', 'cf7_paystack_ajax', [
        'api_url' => home_url('/wp-json/cf7-paystack/v1/initiate-payment'),
    ]);
});

// =======================
// Unified Admin Menu (One Tab with Submenus)
// =======================
add_action('admin_menu', function () use ($cf7_paystack_admin, $cf7_paystack_setup, $cf7_paystack_email) {
    add_menu_page(
        __('CF7 Paystack', 'cf7-paystack'),
        __('CF7 Paystack', 'cf7-paystack'),
        'manage_options',
        'cf7-paystack-dashboard', // Main slug
        [$cf7_paystack_admin, 'transactions_page'], // ✅ Instance method, not static
        'dashicons-money-alt',
        56
    );

    add_submenu_page(
        'cf7-paystack-dashboard',
        __('Transactions', 'cf7-paystack'),
        __('Transactions', 'cf7-paystack'),
        'manage_options',
        'cf7-paystack-dashboard',
        [$cf7_paystack_admin, 'transactions_page']
    );

    add_submenu_page(
        'cf7-paystack-dashboard',
        __('Settings', 'cf7-paystack'),
        __('Settings', 'cf7-paystack'),
        'manage_options',
        'cf7-paystack-setup',
        [$cf7_paystack_setup, 'setup_wizard_page'] // ✅ Instance method
    );

    add_submenu_page(
        'cf7-paystack-dashboard',
        __('Email Template', 'cf7-paystack'),
        __('Email Template', 'cf7-paystack'),
        'manage_options',
        'cf7-paystack-email-template',
        [$cf7_paystack_email, 'email_template_page'] // ✅ Instance method
    );
});

// =======================
// Plugin Meta Settings Link and Author Info
// =======================
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $settings_link = '<a href="admin.php?page=cf7-paystack-setup">' . __('Settings', 'cf7-paystack') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
});

add_filter('plugin_row_meta', function ($links, $file) {
    if ($file == plugin_basename(__FILE__)) {
        $links[] = '<a href="https://stephendache.github.io/" target="_blank">' . __('About the Author', 'cf7-paystack') . '</a>';
    }
    return $links;
}, 10, 2);
