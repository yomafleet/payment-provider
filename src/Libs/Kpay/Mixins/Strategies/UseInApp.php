<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Strategies;

use Yomafleet\PaymentProvider\Libs\Kpay\KpayConfig;

trait UseInApp
{
    abstract public function precreate($payload);
    
    abstract public function getConfig($key = null);

    /**
     * Use In-App type payment.
     *
     * @param array $payload
     * @return array
     */
    public function useInApp($payload)
    {
        $payload['tradeType'] = KpayConfig::APP_TRADE;
        $response = $this->precreate($payload);
        $prepayId = $response['Response']['prepay_id'];
        $orderInfo = [
            'prepay_id'  => $prepayId,
            'merch_code' => $this->getConfig('merchant_code'),
            'appid'      => $this->getConfig('app_id'),
            'timestamp'  => time(),
            'nonce_str'  => $this->sealer()->generateNonce(),
        ];

        $signature = $this->sealer()->generateSignature($orderInfo);

        $sign = $this->sealer()->sign($signature);

        return [
            'order_info' => $orderInfo,
            'sign'       => $sign,
            'sign_type'  => KpayConfig::SIGN_TYPE,
        ];
    }
}
