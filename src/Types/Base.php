<?php

namespace Yomafleet\PaymentProvider\Types;

use ReflectionClass;

abstract class Base
{
    public $config;

    public function __construct()
    {
        $this->config = $this->fetchConfig();
    }

    private function fetchConfig()
    {
        $name = (new ReflectionClass(static::class))->getShortName();

        return config('payment.'.\strtolower($name)) ?: [];
    }
}
