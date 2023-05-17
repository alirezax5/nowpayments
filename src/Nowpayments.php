<?php

namespace Alirezax5\Nowpayments;


class Nowpayments
{
    private $token, $apiKey;
    private bool $sandbox;
    private $sandboxEndPoints = ['invoice', 'status', 'currencies', 'estimate', 'payment', 'invoice-payment', 'min-amount'];
    const API_BASE = 'https://api.nowpayments.io/v1/';
    const API_BASE_SANDBOX = 'https://api-sandbox.nowpayments.io/v1/';

    public function request($method, $path, $body = [])
    {
        $body = array_filter($body);
        $ch = curl_init();
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $method == 'GET' ? $this->getUrl($path) . '?' . http_build_query($body) : $this->getUrl($path),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_MAXREDIRS => 10,

        ];
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_POST, true);
        }
        if ($method != 'auth') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'x-api-key: ' . $this->apiKey,
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->getToken()
            ]);
        }
        curl_setopt_array($ch, $options);
        $res = curl_exec($ch);
        return json_decode($res, true);
    }

    private function getUrl($path): string
    {

        if ($this->sandbox) {
            if (!in_array($path, $this->sandboxEndPoints))
                throw new \Exception('The sending path is not among the sandbox paths');

            return self::API_BASE_SANDBOX . $path;
        }


        return self::API_BASE . $path;
    }

    public function __construct($apiKey, $sandbox = false)
    {
        $this->apiKey = $apiKey;
        $this->sandbox = $sandbox;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function status()
    {
        return $this->request('GET', 'status', []);
    }

    public function auth($email, $password)
    {
        return $this->request('POST', 'auth', compact('email', 'password'));
    }

    public function getAvailableCurrencies(bool $fixed_rate = false)
    {
        return $this->request('GET', 'currencies', compact('fixed_rate'));
    }

    public function getAvailableCurrenciesFull()
    {
        return $this->request('GET', 'full-currencies', []);
    }

    public function getAvailableCheckedCurrencies(bool $fixed_rate = false)
    {
        return $this->request('GET', 'merchant/coins', compact('fixed_rate'));
    }

    public function minAmount(string $currency_from, string $currency_to, string $fiat_equivalent)
    {
        return $this->request('GET', 'min-amount', compact('currency_from', 'currency_to', 'fiat_equivalent'));

    }

    public function createInvoice($price_amount, $price_currency, $ipn_callback_url = null, $order_id = null, $order_description = null, $success_url = null, $cancel_url = null, $is_fee_paid_by_user = null)
    {
        return $this->request('POST', 'invoice', compact('price_amount', 'price_currency', 'ipn_callback_url', 'order_id', 'order_description', 'success_url', 'cancel_url', 'is_fee_paid_by_user'));

    }

    public function createPayment($price_amount, $price_currency, $ipn_callback_url = null, $order_id = null, $order_description = null, $purchase_id = null, $payout_address = null, $payout_currency = null, $payout_extra_id = null, $is_fee_paid_by_user = null)
    {
        return $this->request('POST', 'payment', compact('price_amount', 'price_currency', 'ipn_callback_url', 'order_id', 'order_description', 'purchase_id', 'payout_address', 'payout_currency', 'payout_extra_id', 'is_fee_paid_by_user'));

    }

    public function getEstimatedPice($amount, $currency_from, $currency_to)
    {
        return $this->request('GET', 'estimate', compact('amount', 'currency_from', 'currency_to'));

    }

    public function getPaymentStatus(int $id)
    {
        return $this->request('GET', 'payment/' . $id, []);
    }

    public function getPayments($param)
    {
        return $this->request('GET', 'payment', $param);
    }

    public function getBalance()
    {
        return $this->request('GET', 'payment');
    }

    public function validateAddress($address, $currency, $extra_id = null)
    {
        return $this->request('POST', 'payout/validate-address', compact('address', 'currency', 'extra_id'));
    }

    public function createPayout($address, $currency, $amount, $extra_id = null, $ipn_callback_ur = null, $payout_description = null, $unique_external_id = null, $fiat_amount = null, $fiat_currency = null)
    {
        return $this->request('POST', 'payout', compact('address', 'currency', 'amount', 'extra_id', 'ipn_callback_ur', 'payout_description', 'unique_external_id', 'fiat_amount', 'fiat_currency'));
    }

    public function verifyPayout($id)
    {
        return $this->request('POST', 'payout/' . $id . '/verify');
    }

    public function payoutStatus($id)
    {
        return $this->request('GET', 'payout/' . $id);
    }


    public function getPayouts()
    {
        return $this->request('GET', 'payout');
    }

    public function createConversion($amount, $from_currency, $to_currency)
    {
        return $this->request('POST', 'conversion', compact('amount', 'from_currency', 'to_currency'));
    }

    public function conversionStatus($id)
    {
        return $this->request('GET', 'conversion/' . $id);
    }

    public function getConversions($id = null, $status = null, $from_currency = null, $to_currency = null, $created_at_from = null, $created_at_to = null, $limit = null, $offset = null, $order = null)
    {
        return $this->request('GET', 'conversion', compact('id', 'status', 'from_currency', 'to_currency', 'created_at_from', 'created_at_to', 'limit', 'offset', 'order'));
    }
}