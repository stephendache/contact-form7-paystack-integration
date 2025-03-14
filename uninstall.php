<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'cf7_paystack_transactions';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Clean options
delete_option('cf7_paystack_public_key');
delete_option('cf7_paystack_secret_key');
delete_option('cf7_paystack_currency');
delete_option('cf7_paystack_success_url');
delete_option('cf7_paystack_failure_url');
