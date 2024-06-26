<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay\Mixins;

use Yomafleet\PaymentProvider\Libs\Kpay\KpayHttp;
use Yomafleet\PaymentProvider\Libs\Kpay\KpayConfig;
use Yomafleet\PaymentProvider\Libs\Kpay\KpaySealer;
use Yomafleet\PaymentProvider\Exceptions\KpayRequestFailedException;

trait Refund
{
    abstract public function sealer(): KpaySealer;

    abstract public function getConfig($key = null);

    abstract public function wrapPayload($data);

    /**
     * Request KPay refund.
     *
     * @param array $payload
     * @throws \Yomafleet\PaymentProvider\Exceptions\KpayRequestFailedException
     * @return array
     */
    public function refundRequest(array $payload)
    {
        $content = [
            'appid' => $this->getConfig('app_id'),
            'merch_code' => $this->getConfig('merchant_code'),
            'merch_order_id' => $payload['orderId'],
            'refund_request_no' => $payload['orderId'].time(),
            'refund_reason' => isset($payload['refundReason']) && $payload['refundReason']
                ? $payload['refundReason']
                : 'Reservation cancel'
        ];

        if ($payload['amount']) {
            $content['refund_amount'] = $payload['amount'];
        }

        $data = $this->sealer()->addSignToPayload([
            'timestamp'   => time(),
            'method'      => 'kbz.payment.refund',
            'nonce_str'   => $this->sealer()->generateNonce(),
            'sign_type'   => KpayConfig::SIGN_TYPE,
            'version'     => KpayConfig::VERSION,
            'biz_content' => $content,
        ]);

        $response = KpayHttp::post(
            $this->getConfig('refund_url'),
            $this->wrapPayload($data),
            true,
            $this->logger,
        );

        if ('SUCCESS' !== $response['Response']['result']) {
            throw new KpayRequestFailedException('Kpay Refund failed', $response);
        }

        return $response;
    }

    /**
     * Refund order.
     *
     * @param array $payload
     * @param \callable|null $onError
     * @return array
     */
    public function refund(array $payload, $onError = null)
    {
        try {
            $response = $this->refundRequest($payload);
        } catch (KpayRequestFailedException $th) {
            if (is_callable($onError)) {
                $onError($th->getResponse());
            }
        }

        return $response;
    }

    /**
     * Query refund request.
     *
     * @param array $payload
     * @throws \Yomafleet\PaymentProvider\Exceptions\KpayRequestFailedException
     * @return array
     */
    public function queryRefundRequest(array $payload)
    {
        $content = [
            'appid'           => $this->getConfig('app_id'),
            'merch_code'      => $this->getConfig('merchant_code'),
            'merch_order_id'  => $payload['orderId'],
        ];

        if (array_key_exists('refundId', $payload) && $payload['refundId']) {
            $content['refund_request_no'] = $payload['refundId'];
        }

        $data = $this->sealer()->addSignToPayload([
            'timestamp'   => time(),
            'nonce_str'   => $this->sealer()->generateNonce(),
            'method'      => 'kbz.payment.queryrefund',
            'sign_type'   => KpayConfig::SIGN_TYPE,
            'version'     => KpayConfig::VERSION,
            'biz_content' => $content,
        ]);

        $response = KpayHttp::post(
            $this->getConfig('url').'/queryrefund',
            $this->wrapPayload($data),
            false,
            $this->logger,
        );

        if ('SUCCESS' !== $response['Response']['result']) {
            throw new KpayRequestFailedException('Kpay Query Order request failed', $response);
        }

        return $response;
    }

    /**
     * Query refund order.
     *
     * @param array $payload
     * @param \callable|null $onError
     * @return array
     */
    public function queryRefund(array $payload, $onError = null)
    {
        try {
            $response = $this->queryRefundRequest($payload);
        } catch (KpayRequestFailedException $th) {
            if (is_callable($onError)) {
                $onError($th->getResponse());
            }
        }

        return $response;
    }
}
