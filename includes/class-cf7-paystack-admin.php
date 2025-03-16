<?php  

class CF7_Paystack_Admin {

    public function __construct() {
        // ✅ Add CSV export with nonce protection
        add_action('admin_init', [$this, 'export_csv']);
    }

    // ✅ Transactions Page with Search, Filter, Pagination, and Export
    public function transactions_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cf7_paystack_transactions';

        // ✅ Pagination setup
        $limit = 20;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $limit;

        // ✅ Filtering & Search
        $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';

        $where = 'WHERE 1=1';
        if (!empty($search_query)) {
            $where .= $wpdb->prepare(" AND (email LIKE %s OR reference LIKE %s)", "%$search_query%", "%$search_query%");
        }
        if (!empty($status_filter)) {
            $where .= $wpdb->prepare(" AND status = %s", $status_filter);
        }

        // ✅ Fetch total and paginated results
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name $where");
        $results = $wpdb->get_results("SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");

        // ✅ Start output
        echo '<div class="wrap"><h1>' . __('Paystack Transactions', 'cf7-paystack') . '</h1>';

        // ✅ Search and Filter Form
        echo '<form method="get"><input type="hidden" name="page" value="cf7-paystack-dashboard" />';
        echo '<input type="text" name="s" placeholder="Search Email or Reference..." value="' . esc_attr($search_query) . '" />';
        echo '<select name="status">
                <option value="">' . __('All Status', 'cf7-paystack') . '</option>
                <option value="pending" ' . selected($status_filter, 'pending', false) . '>Pending</option>
                <option value="successful" ' . selected($status_filter, 'successful', false) . '>Successful</option>
                <option value="failed" ' . selected($status_filter, 'failed', false) . '>Failed</option>
              </select>';
        echo '<button type="submit" class="button">' . __('Filter', 'cf7-paystack') . '</button>';
        echo '</form>';

        // ✅ CSV Export Button with Nonce
        echo '<form method="post" style="margin-top: 10px;">
                <input type="hidden" name="cf7_paystack_export_csv" value="1" />
                ' . wp_nonce_field('cf7_paystack_export_csv_action', 'cf7_paystack_export_csv_nonce', true, false) . '
                <button type="submit" class="button button-primary">' . __('Export CSV', 'cf7-paystack') . '</button>
              </form>';

        // ✅ Transactions Table
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Reference</th><th>Email</th><th>Amount (₦)</th><th>Status</th><th>Currency</th><th>Date</th></tr></thead><tbody>';

        if ($results) {
            foreach ($results as $row) {
                echo "<tr>
                    <td>{$row->id}</td>
                    <td>{$row->reference}</td>
                    <td>{$row->email}</td>
                    <td>" . number_format($row->amount, 2) . "</td>
                    <td>{$row->status}</td>
                    <td>{$row->currency}</td>
                    <td>{$row->created_at}</td>
                </tr>";
            }
        } else {
            echo '<tr><td colspan="7">' . __('No transactions found.', 'cf7-paystack') . '</td></tr>';
        }

        echo '</tbody></table>';

        // ✅ Pagination
        $total_pages = ceil($total / $limit);
        if ($total_pages > 1) {
            echo '<div class="tablenav"><div class="tablenav-pages">';
            echo paginate_links([
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo; Prev', 'cf7-paystack'),
                'next_text' => __('Next &raquo;', 'cf7-paystack'),
                'total' => $total_pages,
                'current' => $paged
            ]);
            echo '</div></div>';
        }

        $this->admin_footer_note(); // ✅ Footer Branding
        echo '</div>'; // .wrap end
    }

    // ✅ CSV Export Handler with Nonce
    public function export_csv() {
        if (isset($_POST['cf7_paystack_export_csv'])) {

            // ✅ Verify Nonce for Security
            if (!isset($_POST['cf7_paystack_export_csv_nonce']) || 
                !wp_verify_nonce($_POST['cf7_paystack_export_csv_nonce'], 'cf7_paystack_export_csv_action')) {
                wp_die(__('Security check failed. Please try again.', 'cf7-paystack'));
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'cf7_paystack_transactions';
            $transactions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");

            // ✅ Set headers and output CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=paystack-transactions.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['ID', 'Reference', 'Email', 'Amount', 'Status', 'Currency', 'Date']);

            foreach ($transactions as $row) {
                fputcsv($output, [
                    $row->id,
                    $row->reference,
                    $row->email,
                    $row->amount,
                    $row->status,
                    $row->currency,
                    $row->created_at
                ]);
            }
            fclose($output);
            exit;
        }
    }

    // ✅ Footer Branding
    public function admin_footer_note() {
        echo '<div style="margin-top: 40px; padding: 15px 0; border-top: 1px solid #ccc; text-align: center; color: #555;">
                Built with ❤️ by <a href="https://stephendache.github.io/" target="_blank">Stephen Paul</a>
              </div>';
    }
}
