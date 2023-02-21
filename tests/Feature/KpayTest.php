<?php

namespace Yomafleet\PaymentProvider\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yomafleet\PaymentProvider\Facades\Gateway;
use Yomafleet\PaymentProvider\Tests\TestCase;

class KpayTest extends TestCase
{
    public $gw;

    public function setUp(): void
    {
        parent::setUp();

        Config::set(
            'payment.kpay',
            [
                'url'           => 'http://api.kbzpay.com/payment/gateway/uat',
                'app_id'        => 'example_id',
                'app_key'       => 'example_key',
                'merchant_code' => '12345678',
                'pwa_url'       => 'https://static.kbzpay.com/pgw/uat/pwa/#/',
                'qr'            => [
                    'file_path'  => null,
                ],
            ]
        );

        $this->gw = Gateway::request('kpay');
    }

    public function test_gateway_can_get_kpay_config()
    {
        $this->assertTrue(is_array($this->gw->config));
        $this->assertTrue((bool) count($this->gw->config));
    }

    public function test_kpay_can_generate_nonce_by_given_length()
    {
        $this->assertEquals(5, strlen($this->gw->generateNonce(5)));
        $this->assertEquals(32, strlen($this->gw->generateNonce()));
    }

    public function test_kpay_can_sign_given_envelope()
    {
        $envelope = [
            'timestamp'   => time(),
            'notify_url'  => 'http://localhost/v2/payment/callback/kpay/NEW',
            'nonce_str'   => '845255910308564481',
            'sign_type'   => 'SHA256',
            'method'      => 'kbz.payment.precreate',
            'version'     => '1.0',
            'biz_content' => [
                'merch_order_id' => '201811212009001',
                'merch_code'     => '100001',
                'appid'          => 'kp123456789987654321abcdefghijkl',
                'trade_type'     => 'APPH5',
                'total_amount'   => '1000',
                'trans_currency' => 'MMK',
            ],
        ];

        $signature = $this->gw->generateSignature($envelope, false);
        $signed = $this->gw->sign($signature);

        $this->assertNotEmpty($signed);
    }

    public function test_kpay_precreate_transaction()
    {
        Http::fake([
            '*' => Http::response(['Response' => ['result' => 'SUCCESS']]),
        ]);

        $json = $this->gw->precreate([
            'orderId'     => 'NEW-'.time(),
            'title'       => 'Example Item',
            'amount'      => '1000',
            'type'        => 'NEW',
            'callbackUrl' => 'http://localhost/v2/payment/callback/kpay/NEW',
        ]);

        $this->assertEquals('SUCCESS', $json['Response']['result']);
    }

    public function test_kpay_place_order_transaction()
    {
        ['url' => $url] = $this->gw->withPWALink([
            'prepay_id' => 'KBZ002dd5799389686cf806eff3fd6eabacf3094235191',
        ]);

        $this->assertNotFalse(filter_var($url, FILTER_VALIDATE_URL));
    }

    public function test_kpay_pay_with_qr()
    {
        $preId = '123123';
        $qrCode = '1234567890qwertyuiopasdfghjklzxcvbnm';

        Http::fake([
            '*' => Http::response(['Response' => [
                'result'    => 'SUCCESS',
                'prepay_id' => $preId,
                'qrCode'    => $qrCode,
            ]]),
        ]);

        QrCode::shouldReceive('generate')->once()->andReturn($qrCode);

        ['prepay_id' => $id, 'qr_code' => $code] = $this->gw->pay([
            'orderId'     => 'NEW-'.time(),
            'title'       => 'Example',
            'amount'      => '1000',
            'type'        => 'NEW',
            'callbackUrl' => 'http://localhost/v2/payment/callback/kpay/NEW',
        ]);

        $this->assertEquals($preId, $id);
        $this->assertEquals($qrCode, $code);
    }

    public function test_kpay_pay_with_pwa()
    {
        $preId = '123123';
        Http::fake([
            '*' => Http::response(['Response' => [
                'result'    => 'SUCCESS',
                'prepay_id' => $preId,
            ]]),
        ]);

        ['prepay_id' => $id, 'url' => $url] = $this->gw->pay([
            'orderId'     => 'NEW-'.time(),
            'title'       => 'Example',
            'amount'      => '1000',
            'type'        => 'NEW',
            'callbackUrl' => 'http://localhost/v2/payment/callback/kpay/NEW',
            'usePwa'      => 1,
        ]);

        $this->assertEquals($preId, $id);
        $this->assertNotFalse(filter_var($url, FILTER_VALIDATE_URL));
    }

    public function test_kpay_with_in_app()
    {
        $preId = '123123';
        Http::fake([
            '*' => Http::response(['Response' => [
                'result'    => 'SUCCESS',
                'prepay_id' => $preId,
            ]]),
        ]);

        $response = $this->gw->pay([
            'orderId'       => 'NEW-'.time(),
            'title'         => 'Example',
            'amount'        => '1000',
            'type'          => 'NEW',
            'callbackUrl'   => 'http://localhost/v2/payment/callback/kpay/NEW',
            'useInApp'      => 1,
        ]);

        $this->assertEquals($preId, $response['order_info']['prepay_id']);
        $this->assertTrue(is_string($response['sign']));
    }

    public function test_kpay_pay_with_error_call_provided_callback()
    {
        Http::fake([
            '*' => Http::response(['Response' => [
                'result'    => 'FAILED',
            ]]),
        ]);

        $this->gw->pay([
            'orderId'     => 'NEW-'.time(),
            'title'       => 'Example',
            'amount'      => '1000',
            'type'        => 'NEW',
            'callbackUrl' => 'http://localhost/v2/payment/callback/kpay/NEW',
        ], fn ($response) => $this->assertEquals('FAILED', $response['Response']['result']));
    }
}
