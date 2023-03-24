<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay;

use Illuminate\Support\Facades\Http;

class KpayHttp
{
    /**
     * Post request to given url as json type.
     *
     * @param string $url
     * @param array  $data
     * @param bool $useSSL
     * @return array
     */
    public static function post($url, $data, $useSSL = false)
    {
        $client = Http::asJson();

        if ($useSSL) {
            $config = config('payment.kpay.ssl');
            $client->withOptions([
                'cert' => $config['cert'],
                'ssl_key' => [$config['pem'], $config['password']]
            ]);
        }

        return $client
            ->post($url, $data)
            ->json();
    }
}
