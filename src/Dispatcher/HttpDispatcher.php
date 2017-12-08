<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-06
 * Time: 4:44 PM
 */

namespace Micro\Dispatcher;

use Micro\Http\Response;

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
            return new Response($handler(...array_values($routeArguments)));
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