<?php

namespace Yomafleet\PaymentProvider\Libs\Kpay;

class KpayConfig
{
    public const VERSION = '1.0';

    public const SIGN_TYPE = 'SHA256';

    public const PWA_TRADE = 'PWAAPP';

    public const APP_TRADE = 'APP';

    public const QR_TRADE = 'PAY_BY_QRCODE';

    public const CURRENCY = 'MMK';

    public const TIMEOUT = '60m';

    public const PAYLOAD_WRAP_KEY = 'Request';

    public const RESPONSE_WRPA_KEY = 'Response';
}
