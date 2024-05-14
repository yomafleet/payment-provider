<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay\Mixins;

use Yomafleet\PaymentProvider\Exceptions\KpayRequestFailedException;
use Yomafleet\PaymentProvider\Libs\Kpay\KpayConfig;
use Yomafleet\PaymentProvider\Libs\Kpay\KpayHttp;
use Yomafleet\PaymentProvider\Libs\Kpay\KpaySealer;

trait CloseOrder
{
    abstract public function sealer(): KpaySealer;

    abstract public function getConfig($key = null);

    abstract public function wrapPayload($data);

    /**
     * Close KPay order.
     *
     * @param array $payload
     *
     * @throws \Yomafleet\PaymentProvider\Exceptions\KpayRequestFailedException
     *
     * @return array
     */
    public function closeOrderRequest(array $payload)
    {
        $content = [
            'appid'           => $this->getConfig('app_id'),
            'merch_code'      => $this->getConfig('merchant_code'),
            'merch_order_id'  => $payload['orderId'],
        ];

        $data = $this->sealer()->addSignToPayload([
            'timestamp'   => time(),
            'method'      => 'kbz.payment.closeorder',
            'nonce_str'   => $this->sealer()->generateNonce(),
            'sign_type'   => KpayConfig::SIGN_TYPE,
            'version'     => KpayConfig::VERSION,
            'biz_content' => $content,
        ]);

        $response = KpayHttp::post(
            $this->getConfig('url').'/closeorder',
            $this->wrapPayload($data),
            false,
            $this->logger,
        );

        if ('SUCCESS' !== $response['Response']['result']) {
            throw new KpayRequestFailedException('Kpay order close failed!', $response);
        }

        return $response;
    }

    /**
     * Close order.
     *
     * @param array          $payload
     * @param \callable|null $onError
     *
     * @return array
     */
    public function close($payload, $onError = null)
    {
        try {
            $response = $this->closeOrderRequest($payload);
        } catch (KpayRequestFailedException $th) {
            if (is_callable($onError)) {
                $onError($th->getResponse());
            }
        }

        return $response;
    }
}
