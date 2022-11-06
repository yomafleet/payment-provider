<?php

namespace Yomafleet\PaymentProvider\Tests\Feature;

use Yomafleet\PaymentProvider\Facades\Gateway;
use Yomafleet\PaymentProvider\Tests\TestCase;

class MpgsGatewayTest extends TestCase
{
    public function test_gateway_request()
    {
        Gateway::request();

        $this->assertTrue(true);
    }

    public function test_verify()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('verify')->once()->with(['verify'])->andReturn([
            'success' => true,
        ]);

        Gateway::request()->verify(['verify']);

        $this->assertTrue(true);
    }

    public function test_get_token()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('getToken')->once()->with(['token'])->andReturn([
            'success' => true,
        ]);

        Gateway::request()->getToken(['token']);

        $this->assertTrue(true);
    }

    public function test_delete()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('delete')->once()->with(['delete'])->andReturn([
            'success' => true,
        ]);

        Gateway::request()->delete(['delete']);

        $this->assertTrue(true);
    }

    public function test_authorize()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('authorize')->once()->with(['authorize'], 'token')->andReturn([
            'success' => true,
        ]);

        Gateway::request()->authorize(['authorize'], 'token');

        $this->assertTrue(true);
    }

    public function test_agreement()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('agreement')->once()->with(['agreement'], 'token')->andReturn([
            'success' => true,
        ]);

        Gateway::request()->agreement(['agreement'], 'token');

        $this->assertTrue(true);
    }

    public function test_capture()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('capture')->once()->with(['capture'])->andReturn([
            'success' => true,
        ]);

        Gateway::request()->capture(['capture']);

        $this->assertTrue(true);
    }

    public function test_pay()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('pay')->once()->with(['pay'], 'token')->andReturn([
            'success' => true,
        ]);

        Gateway::request()->pay(['pay'], 'token');

        $this->assertTrue(true);
    }

    public function test_refund()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('refund')->once()->with(['refund'])->andReturn([
            'success' => true,
        ]);

        Gateway::request()->refund(['refund']);

        $this->assertTrue(true);
    }

    public function test_prepay()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('prepay')->once()->with(['prepay'], 'token')->andReturn([
            'success' => true,
        ]);

        Gateway::request()->prepay(['prepay'], 'token');

        $this->assertTrue(true);
    }

    public function test_void()
    {
        $mock = Gateway::shouldReceive('request')->once()->andReturnSelf();

        $mock->shouldReceive('void')->once()->with(['void'])->andReturn([
            'success' => true,
        ]);

        Gateway::request()->void(['void']);

        $this->assertTrue(true);
    }
}
