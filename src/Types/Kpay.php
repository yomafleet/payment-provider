<?php

namespace Yomafleet\PaymentProvider\Types;

use Yomafleet\PaymentProvider\Libs\Kpay\KpayConfig;
use Yomafleet\PaymentProvider\Libs\Kpay\KpaySealer;
use Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Precreate;
use Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Order;

class Kpay extends Base
{
    use Precreate;
    use Order;

    protected $sealer;

    public function __construct(KpaySealer $sealer = null)
    {
        parent::__construct();
        $this->sealer = $sealer ?: new KpaySealer($this->config['app_key']);
    }

    /**
     * Wrap payload with a key according to Kpay provider, while adding signed string.
     *
     * @param array $payload
     *
     * @return array
     */
    protected function wrapPayload($payload)
    {
        return [
            KpayConfig::PAYLOAD_WRAP_KEY => $payload,
        ];
    }

    /**
     * Get Kpay sealer
     *
     * @return \Yomafleet\PaymentProvider\Libs\Kpay\KpaySealer
     */
    public function sealer(): KpaySealer
    {
        return $this->sealer;
    }
}
