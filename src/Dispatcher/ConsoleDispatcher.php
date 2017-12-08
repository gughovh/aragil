<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-06
 * Time: 4:46 PM
 */

namespace Micro\Dispatcher;

use Micro\Console\BaseCommand;
use Micro\Console\Command;
use Micro\Core\Di;

class ConsoleDispatcher extends Dispatcher
{
    /**
     * @return void
     */
    public function dispatch()
    {
        $route = $this->getRoute();
        $options = Di::getInstance()['request']->getConsoleParams();
        $arguments = $this->getRouteArguments();

        if(($handler = $route->getHandler()) instanceof \Closure) {
            /** @var $handler \Closure */
            $command = new BaseCommand();

            $command->setOptions($options);
            $command->setArguments($arguments);
            $handler->call($command);
        } else {
            $commandClass = $route->getController();
            /** @var $command Command*/
            $command = new $commandClass;

            $command->setOptions($options);
            $command->setArguments($arguments);

            $command->{$route->getAction()}();
        }
    }

    /**
     * @return void
     */
    protected function init()
    {
        Command::loadDefaultRoutes();
    }
}