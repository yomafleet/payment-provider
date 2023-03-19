<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay\Mixins;

use Yomafleet\PaymentProvider\Libs\Kpay\KpayHttp;
use Yomafleet\PaymentProvider\Libs\Kpay\KpayConfig;
use Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Strategies\UseQr;
use Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Strategies\UsePwa;
use Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Strategies\UseInApp;
use Yomafleet\PaymentProvider\Exceptions\KpayRequestFailedException;

trait Order
{
    use UseQr;
    use UseInApp;
    use UsePwa;

    /**
     * Order a payment transaction
     *
     * @param array $payload
     * @param \callable|null $onError
     * @return array|false
     */
    public function pay(array $payload, $onError = null)
    {
        $result = false;

        try { // prioritized order by trade type - 'in-app', 'pwa', 'qr'
            if ($payload['useInApp'] ?? false) {
                return $this->useInApp($payload);
            }

            if ($payload['usePwa'] ?? false) {
                return $this->usePwa($payload);
            }

            return $this->useQr($payload);
        } catch (KpayRequestFailedException $exception) {
            if (is_callable($onError)) {
                $onError($exception->getResponse());
            }
        }

        return $result;
    }

    /**
     * Query the latest state of given order
     *
     * @param array $payload
     * @throws \Yomafleet\PaymentProvider\Exceptions\KpayRequestFailedException
     * @return array
     */
    public function queryOrderRequest(array $payload)
    {
        $content = [
            'appid'           => $this->getConfig('app_id'),
            'merch_code'      => $this->getConfig('merchant_code'),
            'merch_order_id'  => $payload['orderId'],
        ];

        if (array_key_exists('refundId', $payload)) {
            $content['refund_request_no'] = $payload['refundId'];
        }

        $data = $this->sealer()->addSignToPayload([
            'timestamp'   => time(),
            'method'      => 'kbz.payment.queryrefund',
            'nonce_str'   => $this->sealer()->generateNonce(),
            'sign_type'   => KpayConfig::SIGN_TYPE,
            'version'     => KpayConfig::VERSION,
            'biz_content' => $content,
        ]);

        $response = KpayHttp::post(
            $this->getConfig('url').'/queryorder',
            $this->wrapPayload($data)
        );

        if ('SUCCESS' !== $response['Response']['result']) {
            throw new KpayRequestFailedException('Kpay Query Order request failed', $response);
        }

        return $response;
    }

    /**
     * Query the latest state of given order
     *
     * @param array $payload
     * @param \callable|null $onError
     * @return array
     */
    public function query(array $payload, $onError = null)
    {
        try {
            $response = $this->queryOrderRequest($payload);
        } catch (KpayRequestFailedException $th) {
            if (is_callable($onError)) {
                $onError($th->getResponse());
            }
        }

        return $response;
    }
}
