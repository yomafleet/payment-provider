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
     *
     * @return array
     */
    public static function post($url, $data)
    {
        return Http::asJson()
            ->post($url, $data)
            ->json();
    }
}
