<?php

namespace Yomafleet\PaymentProvider\Tests\Feature;

use Yomafleet\PaymentProvider\Facades\Gateway;
use Yomafleet\PaymentProvider\Tests\TestCase;

class MPUGatewayTest extends TestCase
{
    public function test_gateway_can_get_mpu_config()
    {
        $gw = Gateway::request('mpu');

        $this->assertTrue(is_array($gw->config));
        $this->assertTrue((bool) count($gw->config));
    }

    public function test_gateway_pay_with_mpu_through_web_interface()
    {
        $gw = Gateway::request('mpu');
        $data = [
            'callback' => 'http://example.com/cb'
        ];

        $view = $gw->prepare($data);
        $viewData = $view->getData()['config'];

        $this->assertEqualsCanonicalizing($data, $viewData);
    }

    public function test_gateway_inquiry_for_mpu_transaction_via_web_interface()
    {
        $gw = Gateway::request('mpu');
        $data = [
            'invoiceNo' => '221227129210009',
            'productDesc' => '221227129210009',
            'amount' => 10000
        ];

        $view = $gw->inquiry($data);
        $viewData = $view->getData();

        $this->assertArrayHasKey('post', $viewData);
        $this->assertArrayHasKey('config', $viewData);
    }
}
