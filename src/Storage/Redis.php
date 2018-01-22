<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 12:08 PM
 */

namespace Aragil\Storage;

use Predis\Client;

class Redis
{
    public static function client()
    {
        return new Client(ini('redis'));
    }
}