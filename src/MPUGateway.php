<?php

namespace Yomafleet\PaymentProvider;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;

trait MPUGateway
{
    protected $payPath = 'Payment/Payment/pay';

    protected $actionPath = 'Payment/Action/api';

    protected $mmkCode = '104';

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

        return view('payment::mpu.checkout', ['data' => $request]);
    }

    public function makePayRequest(array $payload)
    {
        $this->payValidation($payload);

        $payload = [
            'invoiceNo'    => $this->padToFitLength($payload['invoiceNo'], 20),
            'productDesc'  => $payload['productDesc'],
            'amount'       => $this->padToFitLength($payload['amount'] * 100, 12),
            'userDefined1' => isset($payload['userDefined1']) ? $payload['userDefined1'] : '',
            'userDefined2' => isset($payload['userDefined2']) ? $payload['userDefined2'] : '',
            'userDefined3' => isset($payload['userDefined3']) ? $payload['userDefined3'] : '',
            'userDefined3' => isset($payload['userDefined3']) ? $payload['userDefined3'] : '',
            'FrontendURL'  => isset($payload['FrontendURL']) ? $payload['FrontendURL'] : '',
            'BackendURL'   => isset($payload['BackendURL']) ? $payload['BackendURL'] : '',
        ];

        $url = rtrim($this->config['url'], '/').'/'.$this->payPath;

        return [$payload, $url];
    }

    /**
     * Sale Request/Response.
     *
     * @param array $request
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function mpuPay($request)
    {
        return $this->withForm(...$this->makePayRequest($request));
    }

    public function wrapPayload($data)
    {
        $data['merchantID'] = $this->config['merchant_id'];
        $data['currencyCode'] = $this->mmkCode;

        $data = array_filter($data);

        $data['hashValue'] = $this->generateHashValue($data);
        $data['Version'] = '2.8';

        return $data;
    }

    private function padToFitLength($toPad, $length)
    {
        return str_pad($toPad, $length, '0', STR_PAD_LEFT);
    }

    private function createSignatureString($fields)
    {
        sort($fields, SORT_STRING);

        return implode('', array_filter($fields));
    }

    private function generateHashValue($fields)
    {
        $signature = $this->createSignatureString($fields);

        return hash_hmac('sha1', $signature, $this->config['secret'], false);
    }

    public function payValidation($request)
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

    private function withForm($post, $url)
    {
        $this->wrapPayload($post);

        return view('payment::mpu.processing', compact('post', 'url'));
    }

    private function withClient($post, $url)
    {
        $client = new Client([
            'allow_redirects'=> true,
        ]);

        $redir = '';

        $client->post($url, [
            'on_stats' => function (TransferStats $stats) use (&$redir) {
                $redir = (string) $stats->getEffectiveUri();
            },
            'form_params' => array_merge($post, ['hashValue' => $this->generateHashValue($post)]),
        ]);

        return redirect($redir);
    }
}
