<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-06
 * Time: 4:54 PM
 */

namespace Aragil\Console;


use Aragil\Console\Commands\Help;
use Aragil\Console\Commands\Migrate;
use Aragil\Router\Route;

abstract class Command
{
    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @param $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param $argument
     * @param $default
     * @return mixed
     */
    public function arguments($argument = null, $default = null)
    {
        return $this->get('arguments', $argument, $default);
    }

    /**
     * @param $option
     * @param $default
     * @return mixed
     */
    public function options($option = null, $default = null)
    {
        return $this->get('options', $option, $default);
    }

    /**
     * @return string
     */
    public final function getDescription()
    {
        return $this->description;
    }

    /**
     * @return void
     */
    public static final function loadDefaultRoutes()
    {
        Route::console('', Help::class);
        Route::console('help', Help::class);
        Route::prefix('migrate', function () {
            Route::console('{db}', Migrate::class);
        });
    }

    /**
     * @param $text
     */
    protected function line($text)
    {
        echo $text . PHP_EOL;
    }

    /**
     * @param $text
     */
    protected function error($text)
    {
        $this->line($text);
        die(PHP_EOL);
    }

    /**
     * @param $fromWhere
     * @param $key
     * @param $default
     * @return mixed
     */
    private function get($fromWhere, $key, $default)
    {
        if(is_null($key)) {
            return $this->{$fromWhere};
        }
        return $this->{$fromWhere}[$key] ?? $default;
    }
}