<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-06
 * Time: 2:54 PM
 */

namespace Aragil\Request;


class ConsoleRequest extends Request
{
    const DEFAULT_METHOD = 'console';

    private $consoleParams = [];

    private $command;

    protected function init()
    {
        $this->setMethod(self::DEFAULT_METHOD);
        $this->command = $_SERVER['argv'][1] ?? null;
        $argv = array_slice($_SERVER['argv'], 2);

        foreach ($argv as $arg) {
            if(strpos($arg, '=') > 0) {
                list($param, $val) = explode('=', $arg);
                $this->consoleParams[$param] = $val;
            }
        }
    }

    public function input($param = null, $default = null)
    {
        if(is_null($param)) {
            return $this->getConsoleParams();
        }

        return $this->getConsoleParams()[$param] ?? $default;
    }

    public function getConsoleParams()
    {
        return $this->consoleParams;
    }

    public function isHttp()
    {
        return false;
    }

    public function setCalledCommand(string $command)
    {
        $this->command = $command;
    }

    public function setOptions(array $options)
    {
        $this->consoleParams = $options;
    }

    protected function parsePathInfo()
    {
        return array_filter(explode(':', $this->command), function ($i) {
            return trim($i) !== '';
        });
    }
}