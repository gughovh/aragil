<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-06
 * Time: 4:44 PM
 */

namespace Aragil\Dispatcher;

use Aragil\Http\Controller;
use Aragil\Http\Response;

class HttpDispatcher extends Dispatcher
{
    /**
     * @return Response
     */
    public function dispatch()
    {
        $route = $this->getRoute();
        $routeArguments = $this->getRouteArguments();

        if($handler = $route->getHandler()) {
            /** @var $handler \Closure */
            return new Response($handler->call(new Controller(), ...array_values($routeArguments)));
        }

        $controllerClass = $route->getController();
        $controller = new $controllerClass;
        $result = $controller->{$route->getAction()}(...array_values($routeArguments));

        if(!($result instanceof Response)) {
            $result = new Response($result);
        }

        return $result;
    }
}