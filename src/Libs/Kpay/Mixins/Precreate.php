<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay\Mixins;

use Yomafleet\PaymentProvider\Libs\Kpay\KpayHttp;
use Yomafleet\PaymentProvider\Libs\Kpay\KpayConfig;
use Yomafleet\PaymentProvider\Libs\Kpay\KpaySealer;
use Yomafleet\PaymentProvider\Exceptions\KpayPreCreateFailedException;

trait Precreate
{
    abstract public function sealer(): KpaySealer;

    abstract public function getConfig($key = null);

    abstract public function wrapPayload($data);

    /**
     * Request KPay precreate.
     *
     * @param array $payload [orderId, title, amount, type, callbackUrl]
     *
     * @return string
     */
    protected function precreateRequest($payload)
    {
        $extra = [
            'type'           => $payload['type'] ?? '',
            'invoice_number' => $payload['invoice_number'] ?? '',
        ];

        $callbackInfo = urlencode(http_build_query(array_filter($extra)));

        $content = [
            'merch_order_id'  => $payload['orderId'],
            'merch_code'      => $this->getConfig('merchant_code'),
            'appid'           => $this->getConfig('app_id'),
            'trade_type'      => $payload['tradeType'] ?? KpayConfig::QR_TRADE,
            'title'           => $payload['title'],
            'total_amount'    => $payload['amount'],
            'trans_currency'  => KpayConfig::CURRENCY,
            'timeout_express' => KpayConfig::TIMEOUT,
            'callback_info'   => $callbackInfo,
        ];

        $data = $this->sealer()->addSignToPayload([
            'timestamp'   => time(),
            'notify_url'  => $payload['callbackUrl'],
            'method'      => 'kbz.payment.precreate',
            'nonce_str'   => $this->sealer()->generateNonce(),
            'sign_type'   => KpayConfig::SIGN_TYPE,
            'version'     => KpayConfig::VERSION,
            'biz_content' => $content,
        ]);

        return KpayHttp::post(
            $this->config['url'].'/precreate',
            $this->wrapPayload($data)
        );
    }

    /**
     * Precreate order.
     *
     * @param array $payload
     * @throws \Yomafleet\PaymentProvider\Exceptions\KpayPreCreateFailedException
     * @return array
     */
    public function precreate($payload)
    {
        $response = $this->precreateRequest($payload);

        if ('SUCCESS' !== $response['Response']['result']) {
            throw new KpayPreCreateFailedException($response);
        }

        return $response;
    }
}

