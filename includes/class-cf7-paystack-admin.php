<?php
class CF7_Paystack_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    // Add Admin Menu
    public function register_admin_menu() {
        add_menu_page(
            __('CF7 Paystack Transactions', 'cf7-paystack'),
            __('CF7 Paystack Payments', 'cf7-paystack'),
            'manage_options',
            'cf7-paystack-transactions',
            [$this, 'transactions_page'],
            'dashicons-money-alt',
            55
        );
    }

    // Load CSS/JS only on our admin page
    public function enqueue_admin_assets($hook) {
        if ($hook == 'toplevel_page_cf7-paystack-transactions') {
            wp_enqueue_style('cf7-paystack-admin-style', CF7_PAYSTACK_URL . 'assets/css/admin-style.css');
            wp_enqueue_script('cf7-paystack-admin-script', CF7_PAYSTACK_URL . 'assets/js/admin-script.js', ['jquery'], null, true);
        }
    }

    // Transaction page content
    public function transactions_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cf7_paystack_transactions';
        $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 50");

        echo '<div class="wrap"><h1>' . __('Paystack Transactions', 'cf7-paystack') . '</h1>';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Reference</th><th>Email</th><th>Amount</th><th>Status</th><th>Currency</th><th>Date</th></tr></thead><tbody>';

        if ($results) {
            foreach ($results as $row) {
                echo "<tr>
                    <td>{$row->id}</td>
                    <td>{$row->reference}</td>
                    <td>{$row->email}</td>
                    <td>{$row->amount}</td>
                    <td>{$row->status}</td>
                    <td>{$row->currency}</td>
                    <td>{$row->created_at}</td>
                </tr>";
            }
        } else {
            echo '<tr><td colspan="7">' . __('No transactions found.', 'cf7-paystack') . '</td></tr>';
        }

        echo '</tbody></table></div>';
    }
}
