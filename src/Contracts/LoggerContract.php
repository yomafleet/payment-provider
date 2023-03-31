<?php

namespace Yomafleet\PaymentProvider\Contracts;

interface LoggerContract
{
    public function log(string $messge, array $data);
}
