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
        'version'     => env('MPGS_VERSION'),
        'url'         => env('MPGS_GATEWAY_URL'),
        'merchant_id' => env('MPGS_MERCHANT_ID'),
        'operator_id' => env('MPGS_OPERATOR_ID'),
        'password'    => env('MPGS_PASSWORD'),
        'basic_auth'  => env('MPGS_BASIC_AUTH'),
        'js_url'      => env('MPGS_GATEWAY_JS_URL'),
        'auth_attempts' => env('MPGS_AUTH_LIMIT', 3),
    ],

    'kbz' => [
        'url' => env('KBZ_GATEWAY_URL'),
    ],
    'mpu' => [
        'url' => env('MPU_GATEWAY_URL'),
        'merchant_id' => env('MPU_MERCHANT_ID'),
        'secret' => env('MPU_MERCHANT_SECRET'),
    ],
];
