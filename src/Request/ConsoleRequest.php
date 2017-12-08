<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-06
 * Time: 2:54 PM
 */

namespace Micro\Request;


class ConsoleRequest extends Request
{
    const DEFAULT_METHOD = 'console';

    private $consoleParams = [];

    protected function init()
    {
        $this->setMethod('console');
        $argv = array_slice($_SERVER['argv'], 2);

        foreach ($argv as $arg) {
            if(strpos($arg, '=') > 0) {
                list($param, $val) = explode('=', $arg);
                $this->consoleParams[$param] = $val;
            }
        }
    }

    protected function parsePathInfo()
    {
        return array_filter(explode(':', $_SERVER['argv'][1]));
    }

    public function getConsoleParams()
    {
        return $this->consoleParams;
    }
}