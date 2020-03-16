# PHP Web/Cli Request Control
php objective request control for web request or cli request.

- supported web methods: GET, POST, PUT, HEAD, DELETE, PATCH
- CLI Supported
- create manual/global request

Installation
------------

This package is listed on [Packagist](https://packagist.org/packages/mtchabok/request).

```
composer require mtchabok/request
```

How To Usage
------------

#### Create Request Object ####
```php
use \Mtchabok\Database\Request;

$request = new Request();

$request = Request::newAuto();

$request = new Request(Request::METHOD_CLI);

$request = Request::newCli();
```

#### For More Usage Documentation, Use This Request Package By IDE ####