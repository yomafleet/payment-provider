<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay;

use Illuminate\Support\Str;

class KpaySealer
{
    protected $key;

    public function __construct(string $signKey)
    {
        $this->key = $signKey;
    }

    /**
     * Generate NONCE string.
     *
     * @param int $max
     *
     * @return string
     */
    public function generateNonce($max = 32)
    {
        $time = (string) time();
        $length = strlen($time);

        if ($max < $length) {
            return substr($time, 0, $max);
        }

        $random = Str::random($max - strlen($time));

        return "{$random}{$time}";
    }

    /**
     * Sign the signature string according to Kpay.
     *
     * @param string $signature
     *
     * @return string
     */
    public function sign($signature)
    {
        return strtoupper(hash(
            strtolower(KpayConfig::SIGN_TYPE),
            $signature."&key={$this->key}"
        ));
    }

    /**
     * Generate singature string from envelope.
     *
     * @param array $envelope
     *
     * @return string
     */
    public function generateSignature($envelope)
    {
        $collection = collect($envelope)->pipeThrough([
            fn ($col) => $col->forget('sign_type'),
            fn ($col) => $col->mapWithKeys(fn ($v, $k) => is_array($v) ? $v : [$k => $v]), // flatten one level
            fn ($col) => $col->sortKeys(SORT_STRING),
            fn ($col) => $col->filter(),
            fn ($col) => $col->map(fn ($v, $k) => "{$k}={$v}"),
        ]);

        return $collection->join('&');
    }

    /**
     * Add sign to payload.
     *
     * @param array $payload
     *
     * @return string
     */
    public function addSignToPayload($payload)
    {
        $signature = $this->generateSignature($payload);

        $sign = $this->sign($signature);
        $payload['sign'] = $sign;

        return $payload;
    }
}
