<?php
class CF7_Paystack_API
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_endpoints']);
    }

    public function register_endpoints()
    {
        register_rest_route('cf7-paystack/v1', '/initiate-payment', [
            'methods' => 'POST',
            'callback' => [$this, 'initiate_payment'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function initiate_payment(WP_REST_Request $request)
    {
        $form_data = $request->get_params();
        $email = sanitize_email($form_data['email']);
        $amount = isset($form_data['amount']) ? floatval($form_data['amount']) : 0;

        if (empty($email) || $amount <= 0) {
            return new WP_REST_Response(['message' => 'Invalid form data'], 400);
        }

        $reference = uniqid('cf7psk_', true);
        $secret_key = get_option('cf7_paystack_secret_key');
        $currency = get_option('cf7_paystack_currency', 'NGN');
        $callback_url = get_option('cf7_paystack_success_url');

        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'email' => $email,
                'amount' => $amount * 100,
                'currency' => $currency,
                'callback_url' => $callback_url,
                'reference' => $reference
            ]),
        ];

        $response = wp_remote_post('https://api.paystack.co/transaction/initialize', $args);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['data']['authorization_url'])) {
            // Save transaction
            global $wpdb;
            $table_name = $wpdb->prefix . 'cf7_paystack_transactions';
            $wpdb->insert($table_name, [
                'reference' => $reference,
                'email'     => $email,
                'amount'    => $amount,
                'status'    => 'pending',
                'currency'  => $currency,
                'form_data' => maybe_serialize($form_data)
            ]);

            return new WP_REST_Response(['redirect' => $body['data']['authorization_url']], 200);
        }

        return new WP_REST_Response(['message' => 'Failed to initialize payment'], 400);
    }
}
