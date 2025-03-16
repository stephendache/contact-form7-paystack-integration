<?php
class CF7_Paystack_API
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_endpoints']);
    }

    // ✅ Register API Endpoint
    public function register_endpoints()
    {
        register_rest_route('cf7-paystack/v1', '/initiate-payment', [
            'methods' => 'POST',
            'callback' => [$this, 'initiate_payment'],
            'permission_callback' => '__return_true', // Public endpoint
        ]);
    }

    // ✅ Handle Payment Initiation
    public function initiate_payment(WP_REST_Request $request)
    {
        $form_data = $request->get_params();
        $email = sanitize_email($form_data['email']);
        $amount = isset($form_data['amount']) ? floatval($form_data['amount']) : 0;

        // ✅ Basic validation
        if (empty($email) || $amount <= 0) {
            return new WP_REST_Response(['message' => 'Invalid form data. Ensure email and amount are valid.'], 400);
        }

        // ✅ Generate UNIQUE reference based on email and timestamp (clean and traceable)
        $reference = 'cf7psk_' . md5($email . time());

        $secret_key = trim(get_option('cf7_paystack_secret_key'));
        $currency = get_option('cf7_paystack_currency', 'NGN');
        $callback_url = get_option('cf7_paystack_success_url', home_url('/'));
        $failure_url = get_option('cf7_paystack_failure_url', home_url('/'));

        // ✅ Prepare API Request
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => json_encode([
                'email' => $email,
                'amount' => round($amount * 100), // Convert to kobo
                'currency' => $currency,
                'callback_url' => $callback_url,
                'reference' => $reference,
                'metadata' => [
                    'custom_fields' => [
                        ['display_name' => 'CF7 Form', 'variable_name' => 'cf7_form_data', 'value' => json_encode($form_data)],
                        ['display_name' => 'Failure URL', 'variable_name' => 'failure_url', 'value' => $failure_url],
                    ],
                    'source' => 'cf7-paystack-plugin',
                ]
            ]),
            'timeout' => 60
        ];

        // ✅ Send request to Paystack
        $response = wp_remote_post('https://api.paystack.co/transaction/initialize', $args);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // ✅ Handle API Errors
        if (is_wp_error($response)) {
            error_log('Paystack API Error: ' . $response->get_error_message());
            return new WP_REST_Response(['message' => 'Failed to connect to payment gateway.'], 400);
        }

        // ✅ Success: Save Transaction and Return Redirect
        if (isset($body['data']['authorization_url'])) {
            $this->save_transaction($reference, $email, $amount, $currency, $form_data);
            return new WP_REST_Response(['redirect' => esc_url_raw($body['data']['authorization_url'])], 200);
        } else {
            // ✅ Log unexpected error for debugging
            error_log('Paystack API Unexpected Error: ' . print_r($body, true));
            return new WP_REST_Response(['message' => 'Failed to initialize payment.'], 400);
        }
    }

    // ✅ Save Pending Transaction with Unique Reference
    private function save_transaction($reference, $email, $amount, $currency, $form_data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cf7_paystack_transactions';

        // ✅ Avoid duplicate references (only save if not existing)
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE reference = %s", $reference));
        if (!$existing) {
            $wpdb->insert($table_name, [
                'reference' => sanitize_text_field($reference),
                'email'     => sanitize_email($email),
                'amount'    => $amount,
                'status'    => 'pending',
                'currency'  => sanitize_text_field($currency),
                'form_data' => maybe_serialize($form_data),
                'created_at' => current_time('mysql'),
            ]);
        }
    }
}
