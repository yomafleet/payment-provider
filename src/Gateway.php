<?php

namespace Yomafleet\PaymentProvider;

class Gateway
{
    public function request($method = null)
    {
        $method = $method ?? config('payment.default');
        $method = \ucfirst(\strtolower($method));
        $name = __NAMESPACE__.'\\Types\\'.$method;

        return new $name();
    }
}
