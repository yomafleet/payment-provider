<?php

namespace Yomafleet\PaymentProvider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

trait MpgsGateway
{
    public $config;

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function verify($attributes)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$attributes['order_id']}/transaction/{$attributes['transaction_id']}";
        $method = 'PUT';

        $data = [
            'apiOperation' => 'VERIFY',
            'order'        => [
                'currency' => 'MMK',
            ],
            'session' => [
                'id' => $attributes['session_id'],
            ],
        ];

        $verify = $this->request_api($url, $method, $data);

        if ($verify->result !== 'SUCCESS') {
            return [
                'success'       => false,
                'message'       => 'Your card issuer bank has declined. Please contact your bank for support.',
                'error_message' => isset($verify->error) ? $verify->error->explanation : null,
            ];
        }

        $result = $this->getToken($attributes['session_id']);

        if ($result) {
            return ['success' => true, 'data' => $result];
        }

        return $result;
    }

    public function getToken($sessionId)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/token";
        $method = 'POST';
        $data = [
            'session' => [
                'id' => $sessionId,
            ],
            'sourceOfFunds' => [
                'type' => 'CARD',
            ],
        ];

        $response = $this->request_api($url, $method, $data);

        if ($response->result === 'SUCCESS' && $response->status === 'VALID') {
            return $response;
        }
    }

    public function delete($token)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/token/{$token}";
        $method = 'DELETE';
        $response = $this->request_api($url, $method);

        $result['success'] = true;
        if ($response->result !== 'SUCCESS') {
            $result['success'] = false;
            $result['message'] = 'Your card can`t delete!';
            //$result['error_message'] = [$verify->error->cause => [$verify->error->explanation]];
        }

        return $result;
    }

    public function authorize($info, $token)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$info['order_id']}/transaction/{$info['transaction_id']}";

        $method = 'PUT';
        $data = [
            'apiOperation' => 'AUTHORIZE',
            'order'        => [
                'currency' => 'MMK',
                'amount'   => $info['amount'],
            ],
            'sourceOfFunds' => [
                'token'    => $token,
                'provided' => [
                    'card' => [
                        'storedOnFile' => 'STORED',
                    ],
                ],
            ],
            'transaction' => [
                'source' => 'MERCHANT',
            ],
            'agreement' => [
                'type' => 'RECURRING',
                'id'   => $info['agreement_id'],
            ],
        ];

        return $this->request_api($url, $method, $data);
    }

    public function agreement($info, $token)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$info['order_id']}/transaction/{$info['transaction_id']}";

        $method = 'PUT';
        $data = [
            'apiOperation' => 'AUTHORIZE',
            'order'        => [
                'currency' => 'MMK',
                'amount'   => $info['amount'],
            ],
            'sourceOfFunds' => [
                'token'    => $token,
                'provided' => [
                    'card' => [
                        'storedOnFile' => 'TO_BE_STORED',
                    ],
                ],
            ],
            'transaction' => [
                'source' => 'INTERNET',
            ],
            'agreement' => [
                'type' => 'RECURRING',
                'id'   => $info['agreement_id'],
            ],
        ];

        return $this->request_api($url, $method, $data);
    }

    public function capture($info)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$info['order_id']}/transaction/{$info['transaction_id']}";

        $method = 'PUT';
        $data = [
            'apiOperation' => 'CAPTURE',
            'transaction'  => [
                'currency' => 'MMK',
                'amount'   => $info['amount'],
            ],
        ];

        return $this->request_api($url, $method, $data);
    }

    public function pay($info, $token)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$info['order_id']}/transaction/{$info['transaction_id']}";

        $method = 'PUT';
        $data = [
            'apiOperation' => 'PAY',
            'order'        => [
                'currency' => 'MMK',
                'amount'   => $info['amount'],
            ],
            'sourceOfFunds' => [
                'token' => $token,
            ],
        ];

        return $this->request_api($url, $method, $data);
    }

    public function prepay($info, $token)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$info['order_id']}/transaction/{$info['transaction_id']}";

        $method = 'PUT';
        $data = [
            'apiOperation' => 'AUTHORIZE',
            'order'        => [
                'currency' => 'MMK',
                'amount'   => $info['amount'],
            ],
            'sourceOfFunds' => [
                'token' => $token,
            ],
        ];

        return $this->request_api($url, $method, $data);
    }

    public function refund($info)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$info['order_id']}/transaction/{$info['transaction_id']}";

        $method = 'PUT';
        $data = [
            'apiOperation' => 'REFUND',
            'transaction'  => [
                'currency' => 'MMK',
                'amount'   => $info['amount'],
            ],
        ];

        return $this->request_api($url, $method, $data);
    }

    public function void($info)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$info['order_id']}/transaction/{$info['transaction_id']}";

        $method = 'PUT';
        $data = [
            'apiOperation' => 'VOID',
            'transaction'  => [
                'targetTransactionId' => $info['target_transaction_id'],
            ],
        ];

        return $this->request_api($url, $method, $data);
    }

    public function session()
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/session";
       
        $method = 'POST';
        
        $data = [
            'session' => [
                'authenticationLimit' => $this->config['auth_attempts']
            ]
        ];
        
        $response = $this->request_api($url, $method, $data);
        
        if ($response->result !== 'SUCCESS') {
            return [
                'success'       => false,
                'message'       => 'Your card issuer bank has declined. Please contact your bank for support.',
                'error_message' => isset($response->error) ? $response->error->explanation : null,
            ];
        }

        return $response;
    }

    public function initAuthenticate($attributes)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$attributes['order_id']}/transaction/{$attributes['transaction_id']}";

        $method = 'PUT';

        $data = [
            'authentication' => [
                'channel' => 'PAYER_BROWSER'
            ],
            'apiOperation' => 'INITIATE_AUTHENTICATION',
            'order'        => [
                'currency' => 'MMK'
            ],
            'session' => [
                'id' => $attributes['session_id'],
            ]
        ];
        
        $response = $this->request_api($url, $method, $data);
        
        if ($response->result !== 'SUCCESS') {
            return [
                'success'       => false,
                'message'       => 'Your card issuer bank has declined. Please contact your bank for support.',
                'error_message' => isset($response->error) ? $response->error->explanation : null,
            ];
        }

        return $response;
    }

    public function authPayer($attributes)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$attributes['order_id']}/transaction/{$attributes['transaction_id']}";

        $method = 'PUT';

        $data = [
            "authentication" => [
                "redirectResponseUrl" => $this->config['callback_url']
            ],
            'apiOperation' => 'AUTHENTICATE_PAYER',
            'order'        => [
                'currency' => 'MMK',
                'amount' => 10
            ],
            'session' => [
                'id' => $attributes['session_id'],
            ],
            'device' => [
                'browser' => 'MOZILLA',
                'browserDetails' => [
                    '3DSecureChallengeWindowSize' => 'FULL_SCREEN',
                    'acceptHeaders' => 'application/json',
                    'colorDepth' => '24',
                    "javaEnabled" => true,
                    "language" => "en-US",
                    "screenHeight" => 640,
                    "screenWidth" => 480,
                    "timeZone" => 273
                ],
                "ipAddress" => "127.0.0.1"
            ]
        ];

        $response = $this->request_api($url, $method, $data);
        
        if ($response->result !== 'PENDING') {
            return [
                'success'       => false,
                'message'       => 'Your card issuer bank has declined. Please contact your bank for support.',
                'error_message' => isset($response->error) ? $response->error->explanation : null,
            ];
        }

        return $response;
    }

    public function initPay($attributes)
    {
        $url = "{$this->config['url']}{$this->config['merchant_id']}/order/{$attributes['order_id']}/transaction/{$attributes['transaction_id']}";

        $method = 'PUT';

        $data = [
            "authentication" => [
                "transactionId" => $attributes['threeds2_transaction_id']
            ],
            'apiOperation' => 'PAY',
            'order'        => [
                'currency' => 'MMK',
                'amount' => $attributes['amount']
            ],
            'session' => [
                'id' => $attributes['session_id'],
            ]
        ];

        $response = $this->request_api($url, $method, $data);
        
        if ($response->result !== 'SUCCESS') {
            return [
                'success'       => false,
                'message'       => 'Your card issuer bank has declined. Please contact your bank for support.',
                'error_message' => isset($response->error) ? $response->error->explanation : null,
            ];
        }

        return $response;
    }

    private function request_api($url, $method, $data = [])
    {
        $data = json_encode($data);
        $client = new Client();
        $header = [
            'Authorization'  => 'Basic '.base64_encode($this->config['basic_auth']),
            'Content-Type'   => 'Application/json;charset=UTF-8',
            'Content-Length' => strlen($data),
        ];

        try {
            if ($method == 'GET') {
                $response = $client->get($url);
            } else {
                $response = $client->request($method, $url, ['body' => $data, 'headers'=>$header]);
            }

            return json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return json_decode($e->getResponse()->getBody()->getContents());
        }
    }
}
