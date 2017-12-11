<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 11:30 AM
 */

namespace Aragil\Dispatcher;

use Aragil\Core\Di;
use Aragil\Exceptions\NotFoundException;
use Aragil\Request\Request;
use Aragil\Http\Response;
use Aragil\Router\Route;
use Aragil\Router\Router;

abstract class Dispatcher
{
    /**
     * @var Route|null
     */
    private $route = null;

    /**
     * @return Dispatcher
     */
    public static function make()
    {
        $instance = Request::isHttp() ? new HttpDispatcher() : new ConsoleDispatcher();
        $instance->init();
        return $instance;
    }

    /**
     * @return void
     */
    protected function init(){}

    /**
     * @return array
     */
    public function getRouteArguments()
    {
        return $this->getRoute()->getRouteVars(Di::getInstance()['request']->getPathInfo());
    }

    /**
     * @return Route
     */
    protected function getRoute()
    {
        if(is_null($this->route)) {
            $di = Di::getInstance();
            /** @var $request Request*/
            $request = $di['request'];
            $pathInfo = $request->getPathInfo();
            /** @var $router Router*/
            $router = $di['router'];

            $this->route = $router->getMatchedRoute($pathInfo, $request->getMethod());

            if(!($this->route instanceof Route)) {
                throw new NotFoundException();
            }
        }

        return $this->route;
    }

    /**
     * @return Response
     */
    abstract public function dispatch();
}