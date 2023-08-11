<?php

namespace Yomafleet\PaymentProvider\Exceptions;

use Exception;

class KpayRequestFailedException extends Exception
{
    protected $response;

    protected $code;

    public function __construct($message, $response, $code = 0)
    {
        $this->response = $response;
        $this->code = $code;
        parent::__construct($message, $this->code);
    }

    public function getResponse()
    {
        return $this->response;
    }
}
