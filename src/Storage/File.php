<?php
/**
 * Created by PhpStorm.
 * User: comp
 * Date: 12/26/2017
 * Time: 10:25 PM
 */

namespace Aragil\Storage;


use Aragil\Helpers\Log;

class File
{
    const DEFAULT_DELIMITER = '.';
    const HTML_CACHE_DIR = 'html';
    const JSON_CACHE_DIR = 'json';

    public static function getHtmlCache(string $key, $default = null, string $delimiter = self::DEFAULT_DELIMITER)
    {
        $self = new self;
        $cache = $self->getCache($self->getFilePath(self::HTML_CACHE_DIR, $key, 'html', $delimiter, false));

        if($cache === false) {
            return $default;
        }
        return $cache;
    }

    public static function setHtmlCache(string $key, string $html, string $delimiter = self::DEFAULT_DELIMITER)
    {
        $self = new self;
        $bytes = $self->setCache(
            $self->getFilePath(self::HTML_CACHE_DIR, $key, 'html', $delimiter),
            $html
        );

        if ($bytes === false) {
            Log::error("Could not set cache for key - {$key}");
        }
        return $bytes;
    }

    public static function deleteHtmlCache(string $key, string $delimiter = self::DEFAULT_DELIMITER) :void
    {
        $self = new self;
        $self->delete($self->getFilePath(self::HTML_CACHE_DIR, $key, 'html', $delimiter, false));
    }

    public static function getJsonCache(string $key, $default = null, bool $asString = false, string $delimiter = self::DEFAULT_DELIMITER)
    {
        $self = new self;
        $cache = $self->getCache($self->getFilePath(self::JSON_CACHE_DIR, $key, 'json', $delimiter, false));

        if($cache === false) {
            return $default;
        }

        return $asString ? $cache : json_decode($cache, true);
    }

    public static function setJsonCache(string $key, array $array, string $delimiter = self::DEFAULT_DELIMITER)
    {
        $self = new self;
        $bytes = $self->setCache(
            $self->getFilePath(self::JSON_CACHE_DIR, $key, 'json', $delimiter),
            json_encode($array)
        );

        if ($bytes === false) {
            Log::error("Could not set cache for key - {$key}");
        }

        return $bytes;
    }

    public static function deleteJsonCache(string $key, string $delimiter = self::DEFAULT_DELIMITER) :void
    {
        $self = new self;
        $self->delete($self->getFilePath(self::JSON_CACHE_DIR, $key, 'json', $delimiter, false));
    }

    private function getCache(string $file)
    {
        if(file_exists($file)) {
            return file_get_contents($file);
        }

        return false;
    }

    private function storage($path = [], string $delimiter = self::DEFAULT_DELIMITER, bool $create = true) :string
    {
        $storage = CACHE_DIR;

        if(is_string($path)) {
            $path = explode($delimiter, $path);
        }

        if($path && $pathArray = array_filter($path)) {
            while ($dir = array_shift($pathArray)) {
                $storage .= DIRECTORY_SEPARATOR . $dir;
                $create && (is_dir($storage) || mkdir($storage));
            }
        }

        return $storage;
    }

    private function setCache(string $file, string $data)
    {
        return file_put_contents($file, $data);
    }

    private function delete(string $file) :void
    {
        file_exists($file) && unlink($file);
    }

    private function getFilePath(string $cacheDir, string $key, string $extension, string $delimiter, $createDir = true) :string
    {
        $pathInfo = array_filter(explode($delimiter, $key));
        array_unshift($pathInfo, $cacheDir);
        $file = array_pop($pathInfo);

        return $this->storage($pathInfo, $delimiter, $createDir) . DIRECTORY_SEPARATOR . $file . '.' . $extension;
    }
}