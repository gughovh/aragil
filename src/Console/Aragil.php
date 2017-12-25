<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-25
 * Time: 3:45 PM
 */

namespace Aragil\Console;


use Aragil\Core\Application;
use Aragil\Request\ConsoleRequest;

class Aragil
{
    public static function command(string $command, $options = [])
    {
        $appName = 'aragil_' . bin2hex(random_bytes(5));
        $app = new Application($appName, Application::CONSOLE);
        $app->start();
        /** @var $request ConsoleRequest*/
        $request = $app->getDi()['request'];
        $request->setCalledCommand($command);
        $request->setOptions($options);

        $app->handle();
        $app->terminate();
    }
}