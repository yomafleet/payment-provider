<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay\Mixins;

use Yomafleet\PaymentProvider\Exceptions\KpayPreCreateFailedException;
use Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Strategies\UseInApp;
use Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Strategies\UsePwa;
use Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Strategies\UseQr;

trait Order
{
    use UseQr;
    use UseInApp;
    use UsePwa;

    public function pay($payload, $onError = null)
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
        } catch (KpayPreCreateFailedException $exception) {
            if (is_callable($onError)) {
                $onError($exception->getResponse());
            }
        }

        return $result;
    }
}
