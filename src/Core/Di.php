<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 11:16 AM
 */

namespace Micro\Core;


class Di implements \ArrayAccess
{
    private static $di = null;

    private $instances = [];

    private function __construct(){}

    /**
     * @return Di
     */
    public static function getInstance()
    {
        if (is_null(self::$di)) {
            self::$di = new self();
        }

        return self::$di;
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