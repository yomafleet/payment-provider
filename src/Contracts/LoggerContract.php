<?php

namespace Yomafleet\PaymentProvider\Contracts;

interface LoggerContract
{
    public function log(string $message, array $data);
}
