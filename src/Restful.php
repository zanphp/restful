<?php

namespace ZanPHP\Restful;


use FastRoute\DataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use ZanPHP\Contracts\Config\Repository;
use ZanPHP\Support\Singleton;

class Restful extends RouteCollector
{
    use Singleton;

    public function __construct()
    {
        $routeParser = new RouteParser\Std();
        $dataGenerator = new DataGenerator\GroupCountBased();
        parent::__construct($routeParser, $dataGenerator);
        $this->enable();
    }

    public function enable()
    {
        /** @var Repository $repository */
        $repository = make(Repository::class);
        $repository->set("route.router_class", RestfulRouter::class);
    }
}