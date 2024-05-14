<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Strategies;

use Yomafleet\PaymentProvider\Libs\Kpay\KpayConfig;

trait UsePwa
{
    abstract public function precreateRequest(array $payload);

    abstract public function getConfig($key = null);

    /**
     * Use PWA payment type.
     *
     * @param array $payload
     *
     * @return array
     */
    public function usePwa($payload)
    {
        $payload['tradeType'] = KpayConfig::PWA_TRADE;
        $response = $this->precreateRequest($payload);
        $prepayId = $response['Response']['prepay_id'];

        return $this->withPWALink(['prepay_id' => $prepayId]);
    }

    /**
     * Place order in PWAAPP flow, expects an intermediate Kpay Page url.
     *
     * @param array $data ['prepay_id']
     *
     * @return array [$url]
     */
    public function withPWALink($data)
    {
        $signature = $this->sealer()->generateSignature([
            'prepay_id'  => $data['prepay_id'],
            'merch_code' => $this->getConfig('merchant_code'),
            'appid'      => $this->getConfig('app_id'),
            'timestamp'  => time(),
            'nonce_str'  => $this->sealer()->generateNonce(),
        ]);

        $sign = $this->sealer()->sign($signature);

        $queryString = $signature.'&sign='.$sign;

        return [
            'prepay_id' => $data['prepay_id'],
            'url'       => $this->getConfig('pwa_url').'?'.$queryString,
        ];
    }
}
