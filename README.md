Atatus Monitoring of Swoole
==============================

This library enables monitoring of PHP applications powered by [Swoole](https://openswoole.com/) web-server via [Atatus](https://www.atatus.com/) products.

## Installation

The library is to be installed via [Composer](https://getcomposer.org/) as a dependency:

```bash
composer require atatus/swoole-atatus
```

## Usage

### Production

Monitoring of requests from start to finish can be activated by adding a few lines of code to the server entry point.
The monitoring instrumentation is by design completely transparent to an application running on the server.

Install the monitoring instrumentation for all requests:
```php
use Atatus\Swoole;

$page = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Example page</title>
</head>
<body>
    Served by Swoole server
</body>
</html>

HTML;

$server = new \Swoole\Http\Server('127.0.0.1', 8080);
$server->on('request', function ($request, $response) use ($page) {
    // PHP processing within request boundary...
    usleep(1000 * rand(100, 300));

    // Send response
    $response->end($page);

    // PHP processing outside of request boundary...
    usleep(1000 * rand(50, 150));
});

// Application performnce monitoring (APM)
$apm = new AtatusApm(new AtatusApm\TransactionFactory());
$apm->instrument($server);

unset($apm);

$server->start();
```


### Development

Having to install the Atatus PHP extension locally may be inconvenient and outright undesirable for developers.
The workaround is to replace the Atatus reporting functionality with the "stub" implementation doing nothing:

```json
{
    "require": {
        "atatus/swoole-atatus": "*",
        ...
    }
}
```

## Limitations

Concurrent requests subject to [coroutine](https://www.swoole.co.uk/coroutine) multi-tasking are reported as part of the first in-flight transaction.

## Contributing

Pull Requests with fixes and improvements are welcome!

## License

Licensed under the Apache License, Version 2.0.
