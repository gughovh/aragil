<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 1:30 PM
 */

/**
 * @param $key
 * @param null $default
 * @return array|bool|null
 * @throws Exception
 */
function env($key, $default = null)
{
    static $env = null;

    if(is_null($env)) {
        if(!file_exists($envFile = BASE_DIR . DS . '.env')) {
            throw new Exception('The .env file does not exists');
        }
        $env = parse_ini_file($envFile, true, INI_SCANNER_TYPED);
    }

    $keys = explode('.', $key);
    $value = $env;
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
 * @return \Micro\Http\Response
 */
function response($content, $status = 200, $headers = [])
{
    return new \Micro\Http\Response($content, $status, $headers);
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