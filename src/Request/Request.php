<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-04
 * Time: 6:13 PM
 */

namespace Aragil\Request;

use Aragil\Core\Application;

abstract class Request
{
    private $method = null;

    public function __construct()
    {
        $this->setMethod($_SERVER['REQUEST_METHOD'] ?? null);
        $this->init();
    }

    abstract protected function init();

    public static function make(Application $app)
    {
        switch ($app->getType()) {
            case Application::HTTP:
                return new HttpRequest();
            case Application::CONSOLE;
                return new ConsoleRequest();
        }

        throw new \LogicException('Undefined application type');
    }

    abstract public function input($param = null, $default = null);

    /**
     * @return array
     */
    public function getPathInfo()
    {
        return $this->parsePathInfo();
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
     * @return bool
     */
    abstract public function isHttp();

    /**
     * @return array
     */
    abstract protected function parsePathInfo();

}