<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-04
 * Time: 6:13 PM
 */

namespace Micro\Request;

abstract class Request
{
    private $pathInfo;
    private $method = null;

    public function __construct()
    {
        $this->setMethod($_SERVER['REQUEST_METHOD']);
        $this->pathInfo = $this->parsePathInfo();
        $this->init();
    }

    abstract protected function init();

    public static function make()
    {
        return self::isHttp() ? new HttpRequest() : new ConsoleRequest();
    }

    public static function isHttp()
    {
        return !defined('APP_CONSOLE');
    }

    /**
     * @return array
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $method
     */
    protected function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    abstract protected function parsePathInfo();

}