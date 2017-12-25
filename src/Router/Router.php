<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-04
 * Time: 6:30 PM
 */

namespace Aragil\Router;

class Router
{
    const HTTP_ROUTES = 'web';
    const CONSOLE_ROUTES = 'console';
    const ROUTES = [
        self::HTTP_ROUTES,
        self::CONSOLE_ROUTES,
    ];

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @param Route $route
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    /**
     * @param array $pathInfo
     * @param $method
     * @return Route|null
     */
    public function getMatchedRoute(array $pathInfo, $method)
    {
        /** @var $route Route*/
        foreach ($this->routes as $route) {
            if($route->match($pathInfo, $method)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @return void
     */
    public function loadRoutes()
    {
        foreach (self::ROUTES as $route) {
            require ROUTES_DIR . DS . "{$route}.php";
        }
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}