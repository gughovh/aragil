<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-06
 * Time: 4:46 PM
 */

namespace Aragil\Dispatcher;

use Aragil\Console\BaseCommand;
use Aragil\Console\Command;
use Aragil\Core\Di;
use Aragil\Request\ConsoleRequest;

class ConsoleDispatcher extends Dispatcher
{
    private static $defaultRoutesIsLoaded = false;

    /**
     * @return mixed
     */
    public function dispatch()
    {
        $route = $this->getRoute();

        /** @var $consoleRequest ConsoleRequest */
        $consoleRequest = $this->getDi()['request'];
        $options = $consoleRequest->getConsoleParams();
        $arguments = $this->getRouteArguments();

        if(($handler = $route->getHandler()) instanceof \Closure) {
            /** @var $handler \Closure */
            $command = new BaseCommand();

            $command->setOptions($options);
            $command->setArguments($arguments);

            return $handler->call($command);
        } else {
            $commandClass = $route->getController();
            /** @var $command Command*/
            $command = new $commandClass;

            $command->setOptions($options);
            $command->setArguments($arguments);

            return $command->{$route->getAction()}();
        }
    }

    /**
     * @return void
     */
    protected function init()
    {
        if (!self::$defaultRoutesIsLoaded) {
            Command::loadDefaultRoutes();
            self::$defaultRoutesIsLoaded = true;
        }
    }
}