<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 12:08 PM
 */

namespace Aragil\Storage;

class Redis
{
    private static $connectionParams = [
        'host'              =>  null,
        'port'              =>  null,
        'timeout'           =>  null,
        'reserved'          =>  null,
        'retry_interval'    =>  null,
        'read_timeout'      =>  null
    ];

    public static function client()
    {
        self::$connectionParams = array_merge(self::$connectionParams, ini('redis'));
        $client = new \Redis();
        $client->connect(
            self::$connectionParams['host'] ?? 'localhost',
            (int) self::$connectionParams['port'],
            (int) self::$connectionParams['timeout'],
            self::$connectionParams['reserved'] ?? '',
            (int) self::$connectionParams['retry_interval'],
            (int) self::$connectionParams['read_timeout']
        );
        return $client;
    }
}