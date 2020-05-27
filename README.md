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
use \Mtchabok\Request\Request;

$request = Request::newRequest(Request::METHOD_CLI);
$request = Request::newRequestGlobal(['method'=>Request::METHOD_GET]);
```

#### Request Server Details ($_SERVER or local array) ####
```php
use \Mtchabok\Request\Request;
$request = Request::newRequest();
$request->server->HTTP_HOST;
$request->server->getString('REMOTE_ADDR', '127.0.0.1');
$request->server['REQUEST_TIME'];
```

#### Request Query Details ($_GET or local array) ####
```php
use \Mtchabok\Request\Request;
$request = Request::newRequest();
$request->query->foo; // string
$request->query->getNumber('id', 12); // numeric: int or float
$request->query['page']; // string
```

#### Request Post Details ($_POST or local array) ####
```php
use \Mtchabok\Request\Request;
$request = Request::newRequest();
$request->post->first_name;
$request->post->getString('last_name', null, ' -'); // return (string) (isset($_POST['last_name']) ?trim($_POST['last_name'], ' -') :null);
$request->post['mobile'];
```

#### Request Set Data ####
```php
use \Mtchabok\Request\Request;
$request = Request::newRequest();
$request->post->country = 'Iran';
$request->post->set('city', 'Tehran');
$request->get['postal_code'] = '1234567890';
```

#### Request Delete Data ####
```php
use \Mtchabok\Request\Request;
$request = Request::newRequest();
$request->query->delete('postal_code');
unset($request->post->city);
unset($request->post['country']);
```

#### For More Usage Documentation, Use This Request Package By IDE ####