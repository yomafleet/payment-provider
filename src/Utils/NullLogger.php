<?php

namespace Yomafleet\PaymentProvider\Utils;

use Yomafleet\PaymentProvider\Contracts\LoggerContract;

class NullLogger implements LoggerContract
{
    public function log(string $messge, array $data)
    {
    }
}
