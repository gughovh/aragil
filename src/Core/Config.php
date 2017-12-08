<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 11:00 AM
 */

namespace Micro\Core;


class Config
{
    const DEFAULT_PATH_DELIMITER = ".";

    private static $pathDelimiter;

    private $configs;

    public function __construct(array $arrayConfig = null)
    {
        $this->configs = $arrayConfig;
    }

    /**
     * @return Config
     */
    public static function make()
    {
        $configs = [];
        $dir = BASE_DIR . DS . 'config' . DS;

        foreach (glob("{$dir}*.php") as $config) {
            $configs[pathinfo($config, PATHINFO_FILENAME)] = require $config;
        }

        return new self($configs);
    }

    /**
     * @param null $index
     * @param null $defaultValue
     * @return array|mixed|null
     */
    public function get($index = null, $defaultValue = null)
    {
        if(is_null($index)) {
            return $this->configs;
        }

        $keys = explode($this->getPathDelimiter(), $index);
        $value = $this->configs;

        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            }
        }

        return $value ?? $defaultValue;
    }

    /**
     * @param Config $config
     */
    public function merge(Config $config)
    {
        $this->configs = array_merge($this->configs, $config->get());
    }

    /**
     * @param $pathDelimiter
     */
    private function setPathDelimiter($pathDelimiter)
    {
        if(!is_string($pathDelimiter)) {
            throw new \InvalidArgumentException('Invalid path delimiter');
        }
        self::$pathDelimiter = $pathDelimiter;
    }

    /**
     * @return string
     */
    private function getPathDelimiter()
    {
        return self::$pathDelimiter ?? self::DEFAULT_PATH_DELIMITER;
    }
}