<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Available Gateway: "mpgs"
    */

    'default' => env('PAYMENT_GATEWAY', 'mpgs'),

    'mpgs' => [
        'version'       => env('MPGS_VERSION'),
        'url'           => env('MPGS_GATEWAY_URL'),
        'merchant_id'   => env('MPGS_MERCHANT_ID'),
        'operator_id'   => env('MPGS_OPERATOR_ID'),
        'password'      => env('MPGS_PASSWORD'),
        'basic_auth'    => env('MPGS_BASIC_AUTH'),
        'js_url'        => env('MPGS_GATEWAY_JS_URL'),
        'auth_attempts' => env('MPGS_AUTH_LIMIT', 3),
        'callback_url'  => env('MPGS_CALLBACK_URL', 'http://127.0.0.1'),
    ],

    'mpu' => [
        'url'         => env('MPU_GATEWAY_URL'),
        'merchant_id' => env('MPU_MERCHANT_ID'),
        'secret'      => env('MPU_MERCHANT_SECRET'),
    ],

    'kpay' => [
        'url'           => env('KPAY_GATEWAY_URL'),
        'app_id'        => env('KPAY_APP_ID'),
        'app_key'       => env('KPAY_APP_KEY'),
        'merchant_code' => env('KPAY_MERCHANT_CODE'),
        'pwa_url'       => env('KPAY_PWA_URL', 'https://static.kbzpay.com/pgw/uat/pwa/#/'),
        'qr'            => [
            'file_path'  => null,
        ],
    ],
];