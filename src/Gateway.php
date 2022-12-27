<?php

namespace Yomafleet\PaymentProvider;


class Gateway
{
    use MpgsGateway;
    use MPUGateway;

    public $config;

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function request($method = null)
    {
        $default = $method ?? config('payment.default');
        $this->setConfig(config('payment.'.$default));

        return $this;
    }

    protected function mpgs()
    {
        return method_exists($this, 'verify') ? true : false;
    }
}
