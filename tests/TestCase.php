<?php

namespace Yomafleet\PaymentProvider\Tests;

use Yomafleet\PaymentProvider\PaymentGatewayServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            PaymentGatewayServiceProvider::class,
        ];
    }
}
