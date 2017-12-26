<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-07
 * Time: 11:11 AM
 */

namespace Aragil\Console\Commands;


use Aragil\Console\Command;
use Aragil\Core\Di;
use Aragil\Request\ConsoleRequest;
use Aragil\Router\Route;
use Aragil\Router\Router;

class Help extends Command
{
    const CLOSURE_COMMAND_DESCRIPTION = 'See route file, this command has not description, it will call as anonymous function';

    protected $description = 'Show this help message';

    public function handle()
    {
        $di = Di::getInstance();
        /** @var $router Router */
        $router = $di['router'];
        $routes = $router->getRoutes();
        $commandsDescriptions = [];

        $this->line('Available commands:');
        /** @var $route  Route */
        foreach ($routes as $route) {
            if($route->getMethod() != ConsoleRequest::DEFAULT_METHOD) {
                continue;
            }

            if(($handler = $route->getHandler()) instanceof \Closure) {
                $description = self::CLOSURE_COMMAND_DESCRIPTION;
            } else {
                $commandClass = $route->getController();
                /** @var $command Command */
                $command = new $commandClass;
                $description = $command->getDescription();
            }
            $commandsDescriptions[] = trim("{$route->getRouteString()}\t{$description}");
        }

        $commandsDescriptions = array_filter($commandsDescriptions);
        sort($commandsDescriptions);
        foreach ($commandsDescriptions as $description) {
            $this->line($description);
        }
    }


}