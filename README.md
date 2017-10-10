# ZanPHP Simple Restful Router


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
#!/usr/bin/env php
<?php

use Zan\Framework\Foundation\Application;
use ZanPHP\Restful\Restful;
use ZanPHP\HttpFoundation\Response\JsonResponse;

putenv("KDT_RUN_MODE=qatest");

require __DIR__ . '/../vendor/autoload.php';

$appName = 'ZanHttpDemo';
$rootPath = realpath(__DIR__.'/../');

$app = new Application($appName, $rootPath);
$server = $app->createHttpServer();

/** @var Restful $rest */
$rest = Restful::getInstance();

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
/*
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/users
{"users":[]}

chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/user/1
{"id":"1"}

chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/articles/42
{"id":"42","title":"defaultTitle"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/articles/42/universe
{"id":"42","title":"universe"}
*/



$rest->addRoute(['GET', 'POST'], '/cards', function() {
    /** @var  ZanPHP\HttpFoundation\Request\Request $request */
    $request = (yield getContext("request"));
    yield new JsonResponse([
        "method" => $request->getMethod()
    ]);
});
/*
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/cards
{"method":"GET"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/cards -X POST
{"method":"POST"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/cards -X PATCH
{"code":99999,"msg":"PATCH is not allowed","file" ...
*/




$rest->get("/card/{id:\d+}", function($id) {
    yield new JsonResponse([
        "get_id" => $id
    ]);
});
$rest->post("/card/{id:\d+}", function($id) {
    yield new JsonResponse([
        "post_id" => $id
    ]);
});
$rest->put("/card/{id:\d+}", function($id) {
    yield new JsonResponse([
        "put_id" => $id
    ]);
});
$rest->delete("/card/{id:\d+}", function($id) {
    yield new JsonResponse([
        "delete_id" => $id
    ]);
});
$rest->patch("/card/{id:\d+}", function($id) {
    yield new JsonResponse([
        "patch_id" => $id
    ]);
});
/*
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/card/42
{"get_id":"42"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/card/42 -X POST
{"post_id":"42"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/card/42 -X PUT
{"put_id":"42"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/card/42 -X DELETE
{"delete_id":"42"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/card/42 -X PATCH
{"patch_id":"42"}
*/



$rest->addGroup('/admin', function (\FastRoute\RouteCollector $r) {
    $handler = function() { yield new JsonResponse(["users" => "admin"]); };
    $r->get('/do-something', $handler);
    $r->get('/do-another-thing', $handler);
    $r->get('/do-something-else', $handler);
});
/*
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/admin/do-something
{"users":"admin"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/admin/do-another-thing
{"users":"admin"}
chuxiaofengdeMacBook-Pro:~ chuxiaofeng$ curl 127.0.0.1:8030/admin/do-something-else
{"users":"admin"}
*/


$server->start();


```