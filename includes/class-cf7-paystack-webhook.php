<?php

class CF7_Paystack_Webhook {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_webhook']);
    }

    public function register_webhook() {
        register_rest_route('cf7-paystack/v1', '/webhook/', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_webhook'],
            'permission_callback' => '__return_true', // Public for Paystack
        ]);
    }

    public function handle_webhook(WP_REST_Request $request) {
        $body = file_get_contents('php://input'); // Raw payload
        $payload = json_decode($body, true);
        $secret_key = trim(get_option('cf7_paystack_secret_key')); // Ensure trimmed

        // ✅ Signature verification
        $paystack_signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
        $expected_signature = hash_hmac('sha512', $body, $secret_key);

        // 🔴 Signature mismatch
        if ($paystack_signature !== $expected_signature) {
            $this->log_debug("Invalid signature detected for payload: $body");
            return new WP_REST_Response(['message' => 'Invalid signature'], 403);
        }

        // ✅ Check event type and status
        if (isset($payload['event'], $payload['data']['reference'], $payload['data']['status'])
            && strtolower($payload['event']) === 'charge.success'
            && strtolower($payload['data']['status']) === 'success') {

            global $wpdb;
            $reference = sanitize_text_field($payload['data']['reference']);
            $table_name = $wpdb->prefix . 'cf7_paystack_transactions';

            // ✅ Check if transaction exists
            $transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE reference = %s", $reference));

            if (!$transaction) {
                $this->log_debug("Transaction NOT found for reference: $reference");
                return new WP_REST_Response(['message' => 'Transaction not found'], 404);
            }

            // ✅ If already successful, exit quietly
            if ($transaction->status === 'successful') {
                return new WP_REST_Response(['message' => 'Transaction already processed'], 200);
            }

            // ✅ Update to successful and check if updated
            $update_result = $wpdb->update(
                $table_name,
                ['status' => 'successful'],
                ['reference' => $reference],
                ['%s'],
                ['%s']
            );

            if ($update_result === false) {
                $this->log_debug("Failed to update transaction status for reference: $reference. DB Error: " . $wpdb->last_error);
                return new WP_REST_Response(['message' => 'Failed to update transaction'], 500);
            }

            // ✅ Prepare and send emails
            $email_data = [
                'email'     => $transaction->email,
                'amount'    => $transaction->amount,
                'currency'  => $transaction->currency,
                'reference' => $transaction->reference,
            ];

            $this->send_user_email($email_data);
            $this->send_admin_email($email_data);

            $this->log_debug("Transaction {$reference} marked as successful and emails sent.");
            return new WP_REST_Response(['message' => 'Transaction updated and notifications sent.'], 200);
        }

        // 🔴 Invalid event type
        $this->log_debug("Invalid or unhandled event received: " . print_r($payload, true));
        return new WP_REST_Response(['message' => 'Invalid webhook event'], 400);
    }

    /**
     * ✅ Send User Email
     */
    private function send_user_email($data) {
        $email_template = get_option('cf7_paystack_email_template', 'Hello {email}, your payment of {amount} {currency} was successful. Reference: {reference}. Thank you!');
        $user_message = CF7_Paystack_Email::parse_template($email_template, $data);
        wp_mail(sanitize_email($data['email']), __('Payment Successful', 'cf7-paystack'), $user_message);
    }

    /**
     * ✅ Send Admin Email
     */
    private function send_admin_email($data) {
        $admin_email = get_option('admin_email');
        $admin_message = "Hello Admin,\n\nA new payment has been successfully completed.\n\n";
        $admin_message .= "Email: {$data['email']}\nAmount: ₦" . number_format($data['amount'], 2) . "\nCurrency: {$data['currency']}\nReference: {$data['reference']}\n\n";
        $admin_message .= "Login to your dashboard to view transaction details.";
        wp_mail($admin_email, __('New Payment Received (CF7 Paystack)', 'cf7-paystack'), $admin_message);
    }

    /**
     * ✅ Debug Logger
     */
    private function log_debug($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            file_put_contents(__DIR__ . '/webhook-debug-log.txt', '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
        }
    }
}
