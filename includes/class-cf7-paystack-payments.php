<?php
class CF7_Paystack_Payments {

    private $redirect_url = '';

    public function __construct() {
        add_action('wpcf7_before_send_mail', [$this, 'handle_payment'], 10, 3);
        add_filter('wpcf7_ajax_json_echo', [$this, 'custom_redirect_ajax'], 10, 2);
    }

    public function handle_payment($contact_form, &$abort, $submission) {
        $posted_data = $submission->get_posted_data();
        $amount = isset($posted_data['amount']) ? floatval($posted_data['amount']) : 0;
        $email = isset($posted_data['email']) ? sanitize_email($posted_data['email']) : '';

        if ($amount > 0 && !empty($email)) {
            $paystack_url = $this->initiate_paystack_payment($email, $amount, $posted_data);
            if ($paystack_url) {
                $this->redirect_url = $paystack_url;
                $abort = true;
            }
        }
    }

    private function initiate_paystack_payment($email, $amount, $form_data) {
        $secret_key = get_option('cf7_paystack_secret_key');
        $currency = get_option('cf7_paystack_currency', 'NGN');
        $callback_url = get_option('cf7_paystack_success_url');
        $reference = uniqid('cf7psk_', true);

        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type' => 'application/json',
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
            $this->save_transaction($reference, $email, $amount, $currency, $form_data);
            return $body['data']['authorization_url'];
        }

        return false;
    }

    private function save_transaction($reference, $email, $amount, $currency, $form_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cf7_paystack_transactions';

        $wpdb->insert($table_name, [
            'reference' => $reference,
            'email' => $email,
            'amount' => $amount,
            'status' => 'pending',
            'currency' => $currency,
            'form_data' => maybe_serialize($form_data)
        ]);
    }

    public function custom_redirect_ajax($response, $result) {
        if ($this->redirect_url) {
            $response['redirect'] = $this->redirect_url;
            $response['message'] = 'Redirecting you to Paystack. Please wait...';
        }
        return $response;
    }
}
