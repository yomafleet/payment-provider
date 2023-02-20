<?php

namespace Yomafleet\PaymentProvider\Exceptions;

use Exception;

class KpayPreCreateFailedException extends Exception
{
    protected $message = 'Kpay Precreate Failed';

    protected $code = 0;

    protected $response;

    public function __construct($response)
    {
        $this->response = $response;
        parent::__construct($this->message, $this->code);
    }

    public function getResponse()
    {
        return $this->response;
    }
}
