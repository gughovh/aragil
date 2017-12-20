<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 1:30 PM
 */

use Aragil\Core\Config;
use Aragil\Core\Di;

/**
 * @param $key
 * @param null $default
 * @return array|bool|null
 * @throws Exception
 */
function ini($key, $default = null)
{
    static $ini = null;

    if(is_null($ini)) {
        if(!file_exists($iniFile = BASE_DIR . DS . '.ini')) {
            throw new Exception('The .ini file does not exists');
        }
        $ini = parse_ini_file($iniFile, true, INI_SCANNER_TYPED);
    }

    $keys = explode('.', $key);
    $value = $ini;
    while ($_key = array_shift($keys)) {
        if(!array_key_exists($_key, $value)) {
            $value =  $default;
            break;
        }
        $value = $value[$_key];
    }

    return $value;
}

/**
 * @param $content
 * @param int $status
 * @param array $headers
 * @return \Aragil\Http\Response
 */
function response($content, $status = 200, $headers = [])
{
    return new \Aragil\Http\Response($content, $status, $headers);
}

/**
 * @param $headers
 * @return string
 */
function buildHeaders($headers)
{
    $headersStr = '';

    foreach ($headers as $header => $value) {
        $headersStr .= "{$header}: $value";
    }

    return $headersStr;
}

/**
 * @param null $key
 * @param null $default
 * @return array|mixed|null
 */
function config($key = null, $default = null)
{
    $di = Di::getInstance();
    /** @var $config Config */
    $config = $di['config'];
    return $config->get($key, $default);
}

/**
 * @param $params
 * @param array $option
 * @return PDO
 */
function getPdo($params, $option = [])
{
    static $connections = [];

    $key = md5(json_encode(array_merge($params, $option)));

    if(!array_key_exists($key, $connections)) {
        $dsn = "mysql:dbname={$params['dbname']};host={$params['host']}";
        $connections[$key] = new PDO($dsn, $params['username'], $params['password'], $option);
    }

    return $connections[$key];
}

/**
 * @return \Aragil\Request\Request
 */
function request()
{
    static $request = null;

    if(is_null($request)) {
        $di = Di::getInstance();
        $request = $di['request'];
    }

    return $request;
}