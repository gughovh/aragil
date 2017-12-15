<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 11:00 AM
 */

namespace Aragil\Core;


class Config
{
    const DEFAULT_PATH_DELIMITER = ".";

    private static $pathDelimiter;

    private $configs;
    private $eagerLoading;

    public function __construct(array $arrayConfig = null, bool $eagerLoading = true)
    {
        $this->configs = $arrayConfig;
        $this->eagerLoading = $eagerLoading;
    }

    /**
     * @param bool $eagerLoading
     * @return Config
     */
    public static function make($eagerLoading = true)
    {
        $configs = $eagerLoading ? self::loadConfig() : [];

        return new self($configs, $eagerLoading);
    }

    /**
     * @param null $index
     * @param null $defaultValue
     * @return array|mixed|null
     */
    public function get($index = null, $defaultValue = null)
    {
        if(is_null($index)) {
            $this->loadConfigWhenLazyLoadMode();
            return $this->configs;
        }

        $keys = explode($this->getPathDelimiter(), $index);

        $this->loadConfigWhenLazyLoadMode($keys[0]);

        $value = $this->configs;

        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            }
        }

        return $value ?? $defaultValue;
    }

    /**
     * @param Config|array $config
     */
    public function merge($config)
    {
        if($config instanceof self) {
            $this->configs = array_merge($this->configs, $config->get());
        } elseif (is_array($config)) {
            $this->configs = array_merge($this->configs, $config);
        }
    }

    private function loadConfigWhenLazyLoadMode($config = null)
    {
        if($this->eagerLoading
            || ($config
                && array_key_exists($config, $this->configs)
            )
        ) {
            return;
        }

        $this->merge(
            self::loadConfig($config, array_keys($this->configs))
        );
    }

    private static function loadConfig($config = null, $exceptOnes = [])
    {
        $dir = self::getConfigDir() . DS;
        $pattern = $config ?? '*';
        $configs = [];

        foreach (glob("{$dir}{$pattern}.php") as $configFile) {
            $configName = pathinfo($configFile, PATHINFO_FILENAME);

            if(!in_array($configName, $exceptOnes)) {
                $configs[$configName] = require $configFile;
            }
        }

        return $configs;
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

    /**
     * @param $config
     * @return string
     */
    private function getConfigFile($config)
    {
        return self::getConfigDir() . DS . $config . '.php';
    }

    /**
     * @return string
     */
    private static function getConfigDir()
    {
        return BASE_DIR . DS . 'config';
    }
}