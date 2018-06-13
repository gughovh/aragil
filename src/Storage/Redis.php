<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 12:08 PM
 */

namespace Aragil\Storage;

use Aragil\Helpers\Log;

class Redis
{
    private static $client = null;
    private static $connectionParams = [
        'host'              =>  'localhost',
        'port'              =>  6379,
        'timeout'           =>  0.0,
        'reserved'          =>  null,
        'retry_interval'    =>  0,
        'read_timeout'      =>  0.0
    ];

    public static function client($forceReconnect = false)
    {
        if (is_null(self::$client) || $forceReconnect) {
            self::$connectionParams = array_merge(self::$connectionParams, ini('redis', []));
            self::$client = new \Redis();

            try {
                self::$client->connect(
                    self::$connectionParams['host'],
                    (int) self::$connectionParams['port'],
                    (float) self::$connectionParams['timeout'],
                    self::$connectionParams['reserved'],
                    (int) self::$connectionParams['retry_interval'],
                    (float) self::$connectionParams['read_timeout']
                );
            } catch (\RedisException $e) {
                Log::error($e->getMessage());
                return false;
            }
        }

        return self::$client;
    }
}