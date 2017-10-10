# Routing


```json
{
    "name": "zanphp/httpdemo",
    "require": {
        "zanphp/zan": "dev-master",
        "zanphp/restful": "dev-master"
    },
    "minimum-stability": "dev"
}

```

```php
<?php

use Zan\Framework\Foundation\Application;
use ZanPHP\HttpFoundation\Response\JsonResponse;

require __DIR__ . '/../vendor/autoload.php';

$appName = 'ZanHttpDemo';
$rootPath = realpath(__DIR__.'/../');

$app = new Application($appName, $rootPath);
$server = $app->createHttpServer();


/** @var \ZanPHP\Restful\Restful $rest */
$rest = \ZanPHP\Restful\Restful::getInstance();

$rest->addRoute('GET', '/users', function() {
    yield new JsonResponse([
        "users" => []
    ]);
});
// {id} must be a number (\d+)
$rest->addRoute('GET', '/user/{id:\d+}', function($id) {
    yield new JsonResponse([
        "id" => $id
    ]);
});
// The /{title} suffix is optional
$rest->addRoute('GET', '/articles/{id:\d+}[/{title}]', function($id, $title = "defaultTitle") {
    yield new JsonResponse([
        "id" => $id,
        "title" => $title
    ]);
});


$server->start();

```


```text
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/users
{"users":[]}

chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/user/1
{"id":"1"}

chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/articles/42
{"id":"42","title":"defaultTitle"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/articles/42/universe
{"id":"42","title":"universe"}
```