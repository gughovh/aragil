<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13.12.2017
 * Time: 18:38
 */

namespace Aragil\Helpers;

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

    public static function error($error)
    {
        self::write(self::ERROR, $error);
    }

    protected static function write($level, $txt)
    {
        $dir = self::getDir();
        $file = self::OPTIONS[$level];

        $handle = fopen("{$dir}/{$file}","a+");
        $date = date("Y-m-d H:i:s");

        fwrite($handle,"[{$date}] {$txt} \r\n");
        fclose($handle);
    }

    protected static function getDir()
    {
        return LOG_DIR;
    }
}