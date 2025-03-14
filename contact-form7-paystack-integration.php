<?php

/**
 * Plugin Name: Contact Form 7 Paystack Integration
 * Plugin URI: http://techoconference.com/
 * Description: Integrate Paystack payment gateway with Contact Form 7 and handle email sending after successful payment.
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
require_once CF7_PAYSTACK_PATH . 'includes/class-cf7-paystack-api.php'; // ✅ NEW FILE for AJAX Payment Handling

// =======================
// Initialize Core Classes
// =======================
add_action('plugins_loaded', function () {
    new CF7_Paystack_Admin();
    new CF7_Paystack_Setup();
    new CF7_Paystack_Webhook();
    new CF7_Paystack_API(); // ✅ Initiate API Handler
});

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
    // JavaScript for redirect handling after form submission (Paystack checkout)
    wp_enqueue_script('cf7-paystack-redirect', CF7_PAYSTACK_URL . 'assets/js/cf7-paystack-redirect.js', ['jquery'], null, true);

    // ✅ JavaScript to intercept CF7 submission and initiate payment via AJAX
    wp_enqueue_script('cf7-paystack-ajax', CF7_PAYSTACK_URL . 'assets/js/cf7-paystack-ajax.js', ['jquery'], null, true);
});
