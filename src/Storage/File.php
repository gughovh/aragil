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

    private $options = [
        'baseDir' => CACHE_DIR,
        'delimiter' => self::DEFAULT_DELIMITER,
    ];

    public function __construct(array $options = [], string $baseDir = null)
    {
        $this->options = array_merge($this->options, array_filter($options));
    }

    public static function getHtmlCache(string $key, $default = null, string $baseDir = null, string $delimiter = null)
    {
        $self = new self([
            'baseDir' => $baseDir,
            'delimiter' => $delimiter,
        ]);
        $cache = $self->getCache($self->getFilePath(self::HTML_CACHE_DIR, $key, 'html', false));

        if($cache === false) {
            return $default;
        }
        return $cache;
    }

    public static function setHtmlCache(string $key, string $html, string $baseDir = null, string $delimiter = null)
    {
        $self = new self([
            'baseDir' => $baseDir,
            'delimiter' => $delimiter,
        ]);
        $bytes = $self->setCache(
            $self->getFilePath(self::HTML_CACHE_DIR, $key, 'html'),
            $html
        );

        if ($bytes === false) {
            Log::error("Could not set cache for key - {$key}");
        }
        return $bytes;
    }

    public static function deleteHtmlCache(string $key, string $baseDir = null, string $delimiter = null) :void
    {
        $self = new self([
            'baseDir' => $baseDir,
            'delimiter' => $delimiter,
        ]);
        $self->delete($self->getFilePath(self::HTML_CACHE_DIR, $key, 'html', false));
    }

    public static function getJsonCache(string $key, $default = null, bool $asString = false, string $baseDir = null, string $delimiter = null)
    {
        $self = new self([
            'baseDir' => $baseDir,
            'delimiter' => $delimiter,
        ]);
        $cache = $self->getCache($self->getFilePath(self::JSON_CACHE_DIR, $key, 'json', false));

        if($cache === false) {
            return $default;
        }

        return $asString ? $cache : json_decode($cache, true);
    }

    public static function getJsonPath(string $key, bool $check = true, string $baseDir = null, string $delimiter = null)
    {
        $self = new self([
            'baseDir' => $baseDir,
            'delimiter' => $delimiter,
        ]);
        $file = $self->getFilePath(self::JSON_CACHE_DIR, $key, 'json', false);

        if($check && !file_exists($file)) {
            return false;
        }

        return $file;
    }

    public static function setJsonCache(string $key, array $array, string $baseDir = null, string $delimiter = null)
    {
        $self = new self([
            'baseDir' => $baseDir,
            'delimiter' => $delimiter,
        ]);
        $bytes = $self->setCache(
            $self->getFilePath(self::JSON_CACHE_DIR, $key, 'json'),
            json_encode($array)
        );

        if ($bytes === false) {
            Log::error("Could not set cache for key - {$key}");
        }

        return $bytes;
    }

    public static function deleteJsonCache(string $key, string $baseDir = null, string $delimiter = null) :void
    {
        $self = new self([
            'baseDir' => $baseDir,
            'delimiter' => $delimiter,
        ]);
        $self->delete($self->getFilePath(self::JSON_CACHE_DIR, $key, 'json', false));
    }

    private function getCache(string $file)
    {
        if(file_exists($file)) {
            return file_get_contents($file);
        }

        return false;
    }

    private function storage($path = [], bool $create = true) :string
    {
        $storage = $this->options['baseDir'];

        if(is_string($path)) {
            $path = explode($this->options['delimiter'], $path);
        }

        $pathArray = array_filter($path, function ($dir) {
            return $dir !== '';
        });

        if($pathArray) {
            $dir = array_shift($pathArray);
            while ($dir !== null && $dir !== false) {
                $storage .= DS . $dir;
                $create && (is_dir($storage) || mkdir($storage));
                $dir = array_shift($pathArray);
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

    private function getFilePath(string $cacheDir, string $key, string $extension, $createDir = true) :string
    {
        $pathInfo = array_filter(explode($this->options['delimiter'], $key), function ($dir) {
            return $dir !== '';
        });

        array_unshift($pathInfo, $cacheDir);
        $file = array_pop($pathInfo);

        return $this->storage($pathInfo, $createDir) . DIRECTORY_SEPARATOR . $file . '.' . $extension;
    }
}