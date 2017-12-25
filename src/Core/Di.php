<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 11:16 AM
 */

namespace Aragil\Core;


class Di implements \ArrayAccess
{
    private static $dependencyInjectors = [];

    private $instances = [];

    private function __construct(){}

    /**
     * @return Di
     */
    public static function getInstance($appName = 'main')
    {
        if (!array_key_exists($appName, self::$dependencyInjectors)) {
            throw new \LogicException("Does not exists DI for {$appName} application");
        }

        return self::$dependencyInjectors[$appName];
    }

    public static function newInstance($appName)
    {
        if (array_key_exists($appName, self::$dependencyInjectors)) {
            throw new \LogicException("The DI for {$appName} application already created");
        }

        return self::$dependencyInjectors[$appName] = new self();
    }

    public static function removeInstance($appName)
    {
        if (!array_key_exists($appName, self::$dependencyInjectors)) {
            throw new \LogicException("Does not exists DI for {$appName} application");
        }

        unset(self::$dependencyInjectors[$appName]);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->instances);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->instances[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->instances[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->instances[$offset]);
    }
}