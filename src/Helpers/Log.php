<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13.12.2017
 * Time: 18:38
 */

namespace Aragil\Helpers;

use Aragil\Core\Di;
use Aragil\Request\Request;

class Log
{
    const FATAL = 'fatal';
    const ERROR = 'error';
    const DEBUG = 'debug';
    const INFO = 'info';

    const OPTIONS = [
        self::FATAL => 'log_fatal.txt',
        self::ERROR => 'log_errors.txt',
        self::DEBUG => 'log_debug.txt',
        self::INFO  => 'log_info.txt',
    ];

    const VERBOSE = [
        self::FATAL => [
            self::FATAL,
        ],
        self::ERROR => [
            self::FATAL,
            self::ERROR,
        ],
        self::DEBUG => [
            self::FATAL,
            self::ERROR,
            self::DEBUG,
        ],
        self::INFO  => [
            self::FATAL,
            self::ERROR,
            self::DEBUG,
            self::INFO,
        ],
    ];

    public static function fatal(\Throwable $fatal)
    {
        $txt = "{$fatal->getMessage()}\r\n{$fatal->getTraceAsString()} ";
        self::write(self::FATAL, $txt);
    }

    public static function error($error)
    {
        self::write(self::ERROR, $error);
    }

    public static function debug($debug)
    {
        self::write(self::DEBUG, $debug);
    }

    public static function info($info)
    {
        self::write(self::INFO, $info);
    }

    protected static function write($level, $txt)
    {
        /** @var $request Request */
        $request = Di::getInstance()['request'];
        $debug = $request->input('debug');
        $dir = static::getDir();
        $fileName = $dir . DS . self::OPTIONS[$level];

        $handle = fopen($fileName,"a+");
        $date = date("Y-m-d H:i:s");
        $txt = "[{$date}] {$txt} \r\n";

        if($debug) {
            if(in_array($debug, self::VERBOSE[self::INFO])) {
                if (in_array($debug, self::VERBOSE[$level])) {
                    echo $txt;
                }
            } else {
                echo $txt;
            }
        }

        fwrite($handle,$txt);
        fclose($handle);
        chmod($fileName, 0666);
    }

    protected static function getDir()
    {
        return LOG_DIR;
    }
}