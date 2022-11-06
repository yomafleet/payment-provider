## Demo Payment Gatway

Require this package in your composer.json and update composer.

    composer require yomafleet/payment-provider

## Installation

### Laravel 5.x:

After updating composer, add the ServiceProvider to the providers array in config/app.php

    Yomafleet\PaymentProvider\PaymentGatewayServiceProvider::class,

You can optionally use the facade for shorter code. Add this to your facades:

    'Gateway' => Yomafleet\PaymentProvider\Facades\Gateway::class,

## Using

Use the facade:
	use Yomafleet\PaymentProvider\Facades\Gateway;

    $result = Gateway::request();
    return $result->verify($data);

Use `php artisan vendor:publish` to create a config file located at `config/payment.php` which will allow you to define local configurations to change some settings (default paper etc).
You can also use your ConfigProvider to set certain keys.

### Configuration
The defaults configuration settings are set in `config/payment.php`. Copy this file to your own config directory to modify the values. You can publish the config using this command:

    php artisan vendor:publish --provider="Yomafleet\PaymentProvider\PaymentGatewayServiceProvider"

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
