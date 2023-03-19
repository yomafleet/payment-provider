<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay\Mixins\Strategies;

use Yomafleet\PaymentProvider\Libs\Kpay\KpayConfig;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

trait UseQr
{
    abstract public function precreateRequest(array $payload);

    /**
     * Use QR payment type.
     *
     * @param array $payload
     * @return array
     */
    public function useQr($payload)
    {
        $payload['tradeType'] = KpayConfig::QR_TRADE;
        $response = $this->precreateRequest($payload);
        $prepayId = $response['Response']['prepay_id'];

        return $this->withQrSvg([
            'prepay_id' => $prepayId,
            'qr_code'   => $response['Response']['qrCode'],
        ]);
    }

    /**
     * Generate QR with SVG string.
     *
     * @param array $data
     *
     * @return array
     */
    public function withQrSvg($data)
    {
        $filePath = $this->config['qr']['file_path'];

        if ($filePath) {
            $filePath = rtrim($filePath, '/').'/'.$data['prepay_id'].'.svg';
        }

        $data['qr_code'] = (string) QrCode::generate($data['qr_code'], $filePath);

        return $data;
    }
}
