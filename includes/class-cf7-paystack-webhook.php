<?php

class CF7_Paystack_Webhook {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_webhook']);
    }

    /**
     * ✅ Register Paystack Webhook endpoint
     */
    public function register_webhook() {
        register_rest_route('cf7-paystack/v1', '/webhook/', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_webhook'],
            'permission_callback' => '__return_true', // Public for Paystack to access
        ]);
    }

    /**
     * ✅ Handle Paystack Webhook callback
     */
    public function handle_webhook(WP_REST_Request $request) {
        $body = file_get_contents('php://input'); // Get raw body
        $payload = json_decode($body, true); // Decode JSON

        // ✅ Retrieve stored Paystack secret key
        $secret_key = trim(get_option('cf7_paystack_secret_key'));

        // ✅ Signature Verification
        $paystack_signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
        $expected_signature = hash_hmac('sha512', $body, $secret_key);

        // 🚨 If signature mismatch, return 403
        if ($paystack_signature !== $expected_signature) {
            return new WP_REST_Response(['message' => 'Invalid signature'], 403);
        }

        // ✅ Optional: Log webhook payload for debugging (remove in production)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            file_put_contents(__DIR__ . '/webhook-log.txt', $body . PHP_EOL, FILE_APPEND);
        }

        // ✅ Process only charge.success event with correct structure
        if (
            isset($payload['event']) && strtolower($payload['event']) === 'charge.success' &&
            isset($payload['data']['reference'], $payload['data']['status']) &&
            strtolower($payload['data']['status']) === 'success'
        ) {
            global $wpdb;
            $reference = sanitize_text_field($payload['data']['reference']);
            $table_name = $wpdb->prefix . 'cf7_paystack_transactions';

            // ✅ Find transaction by reference
            $transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE reference = %s", $reference));

            // 🚨 If transaction not found, return error
            if (!$transaction) {
                return new WP_REST_Response(['message' => 'Transaction not found'], 404);
            }

            // ✅ If already successful, return OK (idempotent)
            if ($transaction->status === 'successful') {
                return new WP_REST_Response(['message' => 'Transaction already processed'], 200);
            }

            // ✅ Update transaction status to 'successful'
            $wpdb->update(
                $table_name,
                ['status' => 'successful'],
                ['reference' => $reference],
                ['%s'],
                ['%s']
            );

            // ✅ Prepare dynamic data for emails
            $email_data = [
                'email'     => $transaction->email,
                'amount'    => $transaction->amount,
                'currency'  => $transaction->currency,
                'reference' => $transaction->reference,
            ];

            // ✅ Fetch and parse user email template
            $email_template = get_option('cf7_paystack_email_template', 'Hello {email}, your payment of {amount} {currency} was successful. Reference: {reference}. Thank you!');
            $user_message = CF7_Paystack_Email::parse_template($email_template, $email_data);

            // ✅ Send success email to customer
            wp_mail(
                sanitize_email($transaction->email),
                __('Payment Successful', 'cf7-paystack'),
                $user_message
            );

            // ✅ Prepare admin notification email
            $admin_email = get_option('admin_email');
            $admin_message = "Hello Admin,\n\nA new successful payment has been received via Contact Form 7 + Paystack.\n\n";
            $admin_message .= "Details:\n";
            $admin_message .= "Email: {$transaction->email}\n";
            $admin_message .= "Amount: ₦" . number_format($transaction->amount, 2) . "\n";
            $admin_message .= "Currency: {$transaction->currency}\n";
            $admin_message .= "Reference: {$transaction->reference}\n\n";
            $admin_message .= "Please login to view this transaction in your dashboard.\n\nRegards.";

            // ✅ Send admin notification
            wp_mail($admin_email, __('New Payment Received (CF7 Paystack)', 'cf7-paystack'), $admin_message);

            // ✅ Return success response
            return new WP_REST_Response(['message' => 'Transaction updated and notifications sent.'], 200);
        }

        // ❌ Invalid or unsupported event
        return new WP_REST_Response(['message' => 'Invalid webhook event'], 400);
    }
}
