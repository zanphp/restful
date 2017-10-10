<?php


require __DIR__  . "/../src/Call.php";

function handler2($id, $name) { }
class S {
    public function m($id, $name) {}
    public static function static_m($id, $name) {}
}

$handlers = [];
$handlers[] = function($id, $name) { };
$handlers[] = "handler2";
$handlers[] = [new S, "m"];
$handlers[] = [S::class, "static_m"];

foreach ($handlers as $handler) {
    $closure = \ZanPHP\Restful\Call::toClosure($handler);
    $reflect = new \ReflectionFunction($closure);

    $vars = ["name" => "xiaofeng", "id" => 42];
    $args = [];
    $params = $reflect->getParameters();

    foreach ($params as $param) {
        $paramName = $param->getName();
        if (isset($vars[$paramName])) {
            $args[] = $vars[$paramName];
        } else {
            $args[] = $param->getDefaultValue();
        }
    }
    var_dump($args);
}
