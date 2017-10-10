<?php

namespace ZanPHP\Restful;


use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use Zan\Framework\Store\NoSQL\Exception;
use ZanPHP\HttpFoundation\Exception\PageNotFoundException;
use ZanPHP\HttpFoundation\Request\Request;
use ZanPHP\Routing\IRouter;

class RestfulRouter implements IRouter
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    private static $handlerParameters = [];

    public function __construct()
    {
        $routeData = Restful::getInstance()->getData();
        $this->dispatcher = new GroupCountBased($routeData);
    }

    public function dispatch(Request $request)
    {
        $uri = $request->server->get('REQUEST_URI');
        $method = $request->getMethod();

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($method, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new PageNotFoundException("url not found", 404);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                // $allowedMethods = $routeInfo[1];
                throw new PageNotFoundException("$method is not allowed", 405);
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                if (!is_callable($handler)) {
                    throw new Exception("Internal Error", 500);
                }

                $vars = $routeInfo[2];
                $args = $this->getArgs($uri, $method, $handler, $vars);
                return [null, null, $handler, $args];
        }
        return false;
    }

    private function getArgs($uri, $method, callable $handler, array $vars)
    {
        $k = "$method::$uri";
        if (!isset(self::$handlerParameters[$k])) {
            $closure = Call::toClosure($handler);
            $reflect = new \ReflectionFunction($closure);
            $params = $reflect->getParameters();

            self::$handlerParameters[$k] = [];
            foreach ($params as $param) {
                $name = $param->getName();
                if ($param->isDefaultValueAvailable()) {
                    $defVal = $param->getDefaultValue();
                } else {
                    $defVal = null;
                }
                self::$handlerParameters[$k][$name] = $defVal;
            }

        }

        $params = self::$handlerParameters[$k];

        $args = [];
        foreach ($params as $name => $defVal) {
            if (isset($vars[$name])) {
                $args[] = $vars[$name];
            } else {
                $args[] = $defVal;
            }
        }

        return $args;
    }
}