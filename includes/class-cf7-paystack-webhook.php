<?php
class CF7_Paystack_Webhook {

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_webhook']);
    }

    public function register_webhook() {
        register_rest_route('cf7-paystack/v1', '/webhook/', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_webhook'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function handle_webhook(WP_REST_Request $request) {
        $body = json_decode(file_get_contents('php://input'), true);

        if (isset($body['data']['reference'], $body['event']) && $body['event'] === 'charge.success') {
            global $wpdb;
            $table_name = $wpdb->prefix . 'cf7_paystack_transactions';
            $transaction = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE reference = %s", sanitize_text_field($body['data']['reference'])));

            if ($transaction && $transaction->status !== 'successful') {
                // Update transaction
                $wpdb->update($table_name, ['status' => 'successful'], ['reference' => $transaction->reference]);

                // Send email now
                $form_data = maybe_unserialize($transaction->form_data);
                $to = $transaction->email;
                $subject = 'Payment Successful';
                $message = "Hello, your payment was successful.\n\nDetails:\nReference: {$transaction->reference}\nAmount: {$transaction->amount}\nThank you!";
                wp_mail($to, $subject, $message);
            }

            return new WP_REST_Response(['message' => 'Webhook processed.'], 200);
        }

        return new WP_REST_Response(['message' => 'Invalid request.'], 400);
    }
}
