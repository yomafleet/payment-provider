<?php

namespace Yomafleet\PaymentProvider\Types;

use ReflectionClass;
use Yomafleet\PaymentProvider\Contracts\LoggerContract;
use Yomafleet\PaymentProvider\Utils\NullLogger;

abstract class Base
{
    public $config;

    /** @var LoggerContract */
    public $logger;

    public function __construct(LoggerContract $logger = null)
    {
        $this->config = $this->fetchConfig();
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Set logger
     *
     * @param LoggerContract $logger
     * @return void
     */
    public function setLogger(LoggerContract $logger)
    {
        $this->logger = $logger;
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
