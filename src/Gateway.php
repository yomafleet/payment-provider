<?php

namespace Yomastrategic\Payment;

use Yomastrategic\Payment\MpgsGateway;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class Gateway
{
    use MpgsGateway;

    public function request()
    {
        $default = config('payment.default');
        $this->setConfig(config('payment.'.$default));
        
        return $this;
    }
    
    protected function mpgs()
    {
        return method_exists($this, 'verify') ? true : false;
    }
}