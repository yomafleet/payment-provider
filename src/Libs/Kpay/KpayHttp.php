<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay;

use Illuminate\Support\Facades\Http;
use Yomafleet\PaymentProvider\Contracts\LoggerContract;
use Yomafleet\PaymentProvider\Utils\NullLogger;

class KpayHttp
{
    /**
     * Post request to given url as json type.
     *
     * @param string $url
     * @param array  $data
     * @param bool   $useSSL
     * @param LoggerContract $logger
     *
     * @return array
     */
    public static function post($url, $data, $useSSL = false, LoggerContract $logger = null)
    {
        $client = Http::asJson();

        if ($useSSL) {
            $config = config('payment.kpay.ssl');
            $client->withOptions([
                'verify'  => $config['cacert_path'],
                'cert'    => [$config['cert_path'], $config['cert_pass']],
                'ssl_key' => $config['key_path'],
            ]);
        }

        $response = $client
            ->post($url, $data)
            ->json();

        if (! $logger) {
            $logger = new NullLogger;
        }

        $logger->log('Kpay provider request and response', [
            'request' => $data,
            'response' => $response
        ]);

        return $response;
    }
}
