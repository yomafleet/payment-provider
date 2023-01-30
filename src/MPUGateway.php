<?php

namespace Yomafleet\PaymentProvider;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

trait MPUGateway
{
    public $config;

    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Preparing and sending payment.
     *
     * @param array $request
     *
     * @return view
     */
    public function prepare($request)
    {
        if (!array_key_exists('callback', $request)) {
            return false;
        }

        $config = [
            'callback' => $request['callback'],
        ];

        return view('payment.mpu.checkout', compact('config'));
    }

    /**
     * Inquiry Request/Response.
     *
     * @param array $request
     *
     * @return view
     */
    public function inquiry($request)
    {
        $this->validation($request);

        $post = [
            'merchantID'   => $this->config['merchant_id'],
            'invoiceNo'    => $request['invoiceNo'],
            'productDesc'  => $request['productDesc'],
            'amount'       => $request['amount'],
            'currencyCode' => 104, // mmk format
            'userDefined1' => array_key_exists('userDefined1', $request) ? $request['userDefined1'] : '',
            'userDefined2' => array_key_exists('userDefined2', $request) ? $request['userDefined2'] : '',
            'userDefined3' => array_key_exists('userDefined3', $request) ? $request['userDefined3'] : '',
        ];

        return $this->withForm($post);
    }

    private function create_signature_string($input_fields_array)
    {
        sort($input_fields_array, SORT_STRING);

        $signature_string = '';

        foreach ($input_fields_array as $value) {
            if ($value != '') {
                $signature_string .= $value;
            }
        }

        return $signature_string;
    }

    private function generate_hash_value($input_fields_array)
    {
        $signature_string = $this->create_signature_string($input_fields_array);

        $hash_value = hash_hmac('sha1', $signature_string, $this->config['secret'], false);
        $hash_value = strtoupper($hash_value);

        return $hash_value;
    }

    private function validation($request)
    {
        if (!array_key_exists('invoiceNo', $request)) {
            return false;
        }

        if (!array_key_exists('productDesc', $request)) {
            return false;
        }

        if (!array_key_exists('amount', $request)) {
            return false;
        }
    }

    private function withForm($post)
    {
        $config = [
            'secret'      => $this->config['secret'],
            'gateway_url' => $this->config['url'],
        ];

        return view('payment.mpu.processing', compact('post', 'config'));
    }

    private function withClient($post)
    {
        $client = new Client([
            'allow_redirects'=> true,
        ]);

        $redir = '';

        $client->post($this->config['url'], [
            'on_stats' => function (TransferStats $stats) use (&$redir) {
                $redir = (string) $stats->getEffectiveUri();
            },
            'form_params' => array_merge($post, ['hashValue' => $this->generate_hash_value($post)]),
        ]);

        return redirect($redir);
    }
}
