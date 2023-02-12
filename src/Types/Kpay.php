<?php

namespace Yomafleet\PaymentProvider\Types;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Kpay extends Base
{
    public const VERSION = '1.0';

    public const SIGN_TYPE = 'SHA256';

    public const PWA_TRADE = 'PWAAPP';

    public const APP_TRADE = 'APP';

    public const QR_TRADE = 'PAY_BY_QRCODE';

    public const CURRENCY = 'MMK';

    public const TIMEOUT = '60m';

    public const PAYLOAD_WRAP_KEY = 'Request';

    public function pay($payload)
    {
        // prioritized order by trade type - 'in-app', 'pwa', 'qr'
        if ($payload['useInApp'] ?? false) {
            return $this->useInApp($payload);
        }

        if ($payload['usePwa'] ?? false) {
            return $this->usePwa($payload);
        }

        return $this->useQr($payload);
    }

    protected function useInApp($payload)
    {
        $payload['tradeType'] = self::APP_TRADE;

        $response = $this->precreate($payload);

        if ('SUCCESS' !== $response['Response']['result']) {
            return false;
        }

        $prepayId = $response['Response']['prepay_id'];
        $orderInfo = [
            'prepay_id'  => $prepayId,
            'merch_code' => $this->config['merchant_code'],
            'appid'      => $this->config['app_id'],
            'timestamp'  => time(),
            'nonce_str'  => $this->generateNonce(),
        ];

        $signature = $this->generateSignature($orderInfo);

        $sign = $this->sign($signature);

        return [
            'order_info' => $orderInfo,
            'sign'       => $sign,
            'sign_type'  => self::SIGN_TYPE,
        ];
    }

    protected function usePwa($payload)
    {
        $payload['tradeType'] = self::PWA_TRADE;

        $response = $this->precreate($payload);

        if ('SUCCESS' !== $response['Response']['result']) {
            return false;
        }

        $prepayId = $response['Response']['prepay_id'];

        return $this->withPWALink(['prepay_id' => $prepayId]);
    }

    protected function useQr($payload)
    {
        $payload['tradeType'] = self::QR_TRADE;

        $response = $this->precreate($payload);

        if ('SUCCESS' !== $response['Response']['result']) {
            return false;
        }

        $prepayId = $response['Response']['prepay_id'];

        return $this->withQrSvg([
            'prepay_id' => $prepayId,
            'qr_code'   => $response['Response']['qrCode'],
        ]);
    }

    /**
     * Precreate order.
     *
     * @param array $payload [orderId, title, amount, type, callbackUrl]
     *
     * @return string
     */
    public function precreate($payload)
    {
        $content = [
            'merch_order_id'  => $payload['orderId'],
            'merch_code'      => $this->config['merchant_code'],
            'appid'           => $this->config['app_id'],
            'trade_type'      => $payload['tradeType'] ?? self::QR_TRADE,
            'title'           => $payload['title'],
            'total_amount'    => $payload['amount'],
            'trans_currency'  => self::CURRENCY,
            'timeout_express' => self::TIMEOUT,
            'callback_info'   => urlencode("type={$payload['type']}"),
        ];

        $data = $this->addSignToPayload([
            'timestamp'   => time(),
            'notify_url'  => $payload['callbackUrl'],
            'method'      => 'kbz.payment.precreate',
            'nonce_str'   => $this->generateNonce(),
            'sign_type'   => self::SIGN_TYPE,
            'version'     => self::VERSION,
            'biz_content' => $content,
        ]);

        return $this->post(
            $this->config['url'].'/precreate',
            $this->wrapPayload($data)
        );
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

    /**
     * Place order in PWAAPP flow, expects an intermediate Kpay Page url.
     *
     * @param array $data ['prepay_id']
     *
     * @return array [$url]
     */
    public function withPWALink($data)
    {
        $signature = $this->generateSignature([
            'prepay_id'  => $data['prepay_id'],
            'merch_code' => $this->config['merchant_code'],
            'appid'      => $this->config['app_id'],
            'timestamp'  => time(),
            'nonce_str'  => $this->generateNonce(),
        ]);

        $sign = $this->sign($signature);

        $queryString = $signature.'&sign='.$sign;

        return [
            'prepay_id' => $data['prepay_id'],
            'url'       => $this->config['pwa_url'].'?'.$queryString,
        ];
    }

    /**
     * Generate NONCE string.
     *
     * @param int $max
     *
     * @return string
     */
    public function generateNonce($max = 32)
    {
        $time = (string) time();
        $length = strlen($time);

        if ($max < $length) {
            return substr($time, 0, $max);
        }

        $random = Str::random($max - strlen($time));

        return "{$random}{$time}";
    }

    /**
     * Sign the signature string according to Kpay.
     *
     * @param string $signature
     *
     * @return string
     */
    public function sign($signature)
    {
        return strtoupper(hash(
            strtolower(self::SIGN_TYPE),
            $signature."&key={$this->config['app_key']}"
        ));
    }

    /**
     * Generate singature string from envelope.
     *
     * @param array $envelope
     *
     * @return string
     */
    public function generateSignature($envelope)
    {
        $collection = collect($envelope)->pipeThrough([
            fn ($col) => $col->forget('sign_type'),
            fn ($col) => $col->mapWithKeys(fn ($v, $k) => is_array($v) ? $v : [$k => $v]), // flatten one level
            fn ($col) => $col->sortKeys(SORT_STRING),
            fn ($col) => $col->sortKeys(SORT_STRING),
            fn ($col) => $col->filter(),
            fn ($col) => $col->map(fn ($v, $k) => "{$k}={$v}"),
        ]);

        return $collection->join('&');
    }

    /**
     * Wrap payload with a key according to Kpay provider, while adding signed string.
     *
     * @param array $payload
     *
     * @return array
     */
    protected function wrapPayload($payload)
    {
        return [
            self::PAYLOAD_WRAP_KEY => $payload,
        ];
    }

    /**
     * Add sign to payload.
     *
     * @param array $payload
     *
     * @return string
     */
    protected function addSignToPayload($payload)
    {
        $signature = $this->generateSignature($payload);

        $sign = $this->sign($signature);
        $payload['sign'] = $sign;

        return $payload;
    }

    /**
     * Post request to given url as json type.
     *
     * @param string $url
     * @param array  $data
     *
     * @return array
     */
    protected function post($url, $data)
    {
        return Http::asJson()
            ->post($url, $data)
            ->json();
    }
}
