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

    /**
     * Retrieve config values of the respective payment.
     *
     * @return array
     */
    private function fetchConfig()
    {
        $name = (new ReflectionClass(static::class))->getShortName();

        return config('payment.'.\strtolower($name)) ?: [];
    }

    /**
     * Get all configs or specific one.
     *
     * @param string|null $key
     *
     * @return string|array|null
     */
    public function getConfig($key = null)
    {
        if (!$key) {
            return $this->config;
        }

        return array_key_exists($key, $this->config) ? $this->config[$key] : null;
    }
}
