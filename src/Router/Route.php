<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-04
 * Time: 6:29 PM
 */

namespace Aragil\Router;
use Aragil\Core\Di;
use Aragil\Request\ConsoleRequest;
use Aragil\Request\Request;

/**
 * Class Route
 * @package Aragil\Router
 * @method static get($route, $handler)
 * @method static post($route, $handler)
 * @method static options($route, $handler)
 * @method static prefix($prefix, $handler)
 * @method static console($command, $handler)
 */
class Route
{
    const HTTP_DELIMITER = '/';
    const CONSOLE_DELIMITER = ':';

    const CONTROLLER_NAMESPACE = 'App\Http\Controllers';
    const CONSOLE_COMMAND_NAMESPACE = 'App\Console\Commands';

    const PARENT_ACTIVE_STATUS = 'active';
    const PARENT_CLOSED_STATUS = 'closed';

    // TODO move from here
    const CONSOLE_ACTION = 'handle';

    private static $macros = [
        'get' => [
            'resolveMethod' => 'setHandler'
        ],
        'post' => [
            'resolveMethod' => 'setHandler'
        ],
        'options' => [
            'resolveMethod' => 'setHandler'
        ],
        'prefix' => [
            'resolveMethod' => 'setGroup'
        ],
        'console' => [
            'resolveMethod' => 'setConsoleHandler'
        ]
    ];

    private $prefixes = [];
    private $route;
    private $method = null;
    private $controller = null;
    private $action = null;
//    private $console = null;
    private $handler = null;
    private $variableRouteParams = [];
    private $routeParams = null;
    private static $tree = [];

    public static function __callStatic($name, $arguments)
    {
        if(!array_key_exists($name, self::$macros)) {
            throw new \BadMethodCallException("$name method does not exists");
        }

        if(count($arguments) != 2) {
            throw new \InvalidArgumentException('Invalid route arguments count');
        }

        $self = new self();
        $self->{"_{$name}"}($arguments[0]);
        $self->{self::$macros[$name]['resolveMethod']}($arguments[1]);
    }

    /**
     * @return string
     */
    public function getRouteString()
    {
        return join($this->getDelimiter(), array_merge($this->prefixes, [$this->route]));
    }

    /**
     * @return array
     */
    public function getRouteParams()
    {
        if(is_null($this->routeParams)) {
            $this->routeParams = array_merge($this->prefixes, array_filter(explode($this->getDelimiter(), $this->route)));
        }
        return $this->routeParams;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param array $pathInfo
     * @param $method
     * @return bool
     */
    public function match(array $pathInfo, $method)
    {
        $routeParams = $this->getRouteParams();

        if(strtolower($method) != $this->method
            || count($routeParams) != count($pathInfo)
        ) {
            return false;
        }

        foreach ($routeParams as $index => $routeParam) {
            $reqParam = array_shift($pathInfo);
            // TODO add case to checking param is required or not
            if(!$this->checkParam($routeParam, $reqParam, $index)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $pathInfo
     * @return array
     */
    public function getRouteVars($pathInfo)
    {
        $vars = [];
        foreach ($this->variableRouteParams as $index => $varParam) {
            $vars[$this->getRouteVarName($index)] = $pathInfo[$index];
        }
        return $vars;
    }

    private function getRouteVarName($index)
    {
        preg_match('/^\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}$/', $this->getRouteParams()[$index], $matches);
        return $matches[1];
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @param $prefix
     * @return $this
     */
    private function _prefix($prefix)
    {
        $this->prefixes = is_string($prefix) ? explode($this->getDelimiter(), $prefix) : $prefix;

        return $this;
    }

    /**
     * @param $route
     * @return $this
     */
    private function _get($route)
    {
        $this->addRoute($route, 'get');

        return $this;
    }

    /**
     * @param $route
     * @return $this
     */
    private function _post($route)
    {
        $this->addRoute($route, 'post');

        return $this;
    }

    /**
     * @param $route
     * @return $this
     */
    private function _options($route)
    {
        $this->addRoute($route, 'option');

        return $this;
    }

    /**
     * @param $route
     * @return $this
     */
    private function _console($route)
    {
        $this->addRoute($route, ConsoleRequest::DEFAULT_METHOD);

        return $this;
    }

    /**
     * @param $route
     * @param $method
     */
    private function addRoute($route, $method)
    {
        $this->method = strtolower($method);
        $this->route = $route;
        $this->_prefix($this->getParentPrefixes());

        $di = Di::getInstance();
        $di['router']->addRoute($this);
    }

    /**
     * @return array
     */
    private function getParentPrefixes()
    {
        $prefixes = [];
        foreach (self::$tree as $item) {
            if($item['status'] == self::PARENT_ACTIVE_STATUS) {
                $prefixes = array_merge($prefixes, $item['route']->prefixes);
            }
        }

        return $prefixes;
    }

    /**
     * @param $handler
     */
    private function setHandler($handler)
    {
        if($handler instanceof \Closure) {
            $this->handler = $handler;
        } elseif (is_string($handler)) {
            $this->parseHandler($handler);
        }
    }

    /**
     * @param $handler
     */
    private function setConsoleHandler($handler)
    {
        if($handler instanceof \Closure) {
            $this->handler = $handler;
        } elseif (is_string($handler)) {
            $this->parseConsoleHandler($handler);
        }
    }

    /**
     * @param \Closure $callback
     */
    private function setGroup(\Closure $callback)
    {
        $this->begin();
        $callback();
        $this->end();
    }

    /**
     * @param $handler
     */
    private function parseHandler($handler)
    {
        $params = explode('@', $handler);
        $this->controller = self::CONTROLLER_NAMESPACE . '\\' . $params[0];
        $this->action = $params[1];
    }

    /**
     * @param $handler
     */
    private function parseConsoleHandler($handler)
    {
        $params = explode('@', $handler);
        $this->controller = class_exists($params[0]) ? $params[0] : self::CONSOLE_COMMAND_NAMESPACE . '\\' . $params[0];
        $this->action = $params[1] ?? self::CONSOLE_ACTION;
    }

    /**
     * @param $routeParam
     * @param $reqParam
     * @param $index
     * @return mixed
     */
    private function checkParam($routeParam, $reqParam, $index)
    {
        $checkMethod = $this->getRouteParamCheckMethod($routeParam, $index);
        return $checkMethod($routeParam, $reqParam);
    }

    /**
     * @param $routeParam
     * @param $index
     * @return \Closure
     */
    private function getRouteParamCheckMethod($routeParam, $index)
    {
        switch (true) {
//            case !Request::isHttp():
            default:
                return function ($routeParam, $reqParam) {
                    return $routeParam === $reqParam;
                };
            case preg_match('/^\{[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\}/', $routeParam):
                $this->addAsVarParam($routeParam, $index);
                return $this->varParamsComparator();
        }
    }

    /**
     * @param $routeParam
     * @param $index
     */
    private function addAsVarParam($routeParam, $index)
    {
        $this->variableRouteParams[$index] = $routeParam;
    }

    /**
     * @return \Closure
     */
    private function varParamsComparator()
    {
        return function () {
            return true;
        };
    }

    /**
     * @return void
     */
    private function begin()
    {
        self::$tree[] = [
            'route' => $this,
            'status' => self::PARENT_ACTIVE_STATUS,
        ];
    }

    /**
     * @return void
     */
    private function end()
    {
        for ($i = count(self::$tree) - 1; $i >= 0; $i--) {
            if(self::$tree[$i]['route'] === $this) {
                self::$tree[$i]['status'] = self::PARENT_CLOSED_STATUS;
                break;
            }
        }
    }

    private function getDelimiter()
    {
        return $this->getMethod() == ConsoleRequest::DEFAULT_METHOD ? self::CONSOLE_DELIMITER : self::HTTP_DELIMITER;
    }
}