<?php

namespace ZanPHP\Restful;

/**
 * User: xiaofeng
 * Date: 2016/5/26
 * Time: 15:23
 */

use Closure;
use ReflectionFunction;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
use RuntimeException;

class Call
{

    /**
     * 将callable转换为Closure
     * @param callable $callable
     * @return Closure
     */
    public static function toClosure(callable $callable) {
        // php7
        if (is_callable([Closure::class, "fromCallable"])) {
            return Closure::fromCallable($callable);
        }

        // php5
        try {
            if (is_object($callable)) {
                return self::getObjectCallableClosure($callable);
            }

            if (is_string($callable)) {
                return self::getStringCallableClosure($callable);
            }

            if (is_array($callable)) {
                return self::getArrayCallableClosure($callable);
            }
        } catch (ReflectionException $ex) {
            throw new RuntimeException($ex->getMessage(), $ex->getCode());
        }

        throw new RuntimeException("Can Not Get Closure From Callable");
    }

    /**
     * 获取可调用对象的闭包
     * 可调用对象:
     * 1. \Closure
     * 2. 实现了__invoke魔术方法的类实例
     * (is_callable(class with method __invoke) === true)
     *
     * @param $callable
     * @return Closure
     */
    protected static function getObjectCallableClosure($callable)
    {
        if ($callable instanceof Closure) {
            return $callable;
        }

        $method = new ReflectionMethod($callable, "__invoke");
        return $method->getClosure($callable);
    }

    /**
     * 获取可调用字符串的闭包
     * 可调用字符串
     * 1. class::method | static::method
     * 2. function name
     *
     * @param string $callable
     * @return Closure
     */
    protected static function getStringCallableClosure($callable)
    {
        if (strpos($callable, "::") === false) {
            $function = new ReflectionFunction($callable);
            return $function->getClosure();
        }

        list($clazz, $method) = explode("::", $callable);
        if ($clazz === "static") {
            throw new RuntimeException("Still Not Implement");
        }

        $method = new ReflectionMethod($clazz, $method);
        return $method->getClosure();
    }

    /**
     * 获取可调用数组的闭包
     * 可调用数组:
     * 1. ["class", "[self|parent|static::]method"]
     * 2. [object, "[self|parent|static::]method"]
     *
     * @param array $callable
     * @return Closure
     */
    protected static function getArrayCallableClosure(array $callable)
    {
        list($clazz, $method) = $callable;

        if(strpos($method, "::") === false) {
            $method = new ReflectionMethod($clazz, $method);
            return $method->getClosure(is_object($clazz) ? $clazz : null);
        }

        list($clazzScope, $method) = explode("::", $method);

        if ($clazzScope === "self") {
            $method = new ReflectionMethod($clazz, $method);
            return $method->getClosure(is_object($clazz) ? $clazz : null);
        }

        if ($clazzScope === "parent") {
            if (is_object($clazz)) {
                $subClazz = new ReflectionClass($clazz);
                $parentClazz = $subClazz->getParentClass();
                $parentMethod = $parentClazz->getMethod($method);
                return $parentMethod->getClosure($clazz); // non-static method ===> non-static Closure
            } else if (is_string($clazz)) {
                $clazz = get_parent_class($clazz);
                $method = new ReflectionMethod($clazz, $method);
                return $method->getClosure(null); // static method ==> static Closure
            }
        }

        if ($clazzScope === "static") {
            throw new RuntimeException("Still Not Implement");
        }

        throw new RuntimeException("Can Not Get A Closure From The Array Callable");
    }
}