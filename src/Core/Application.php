<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-04
 * Time: 6:08 PM
 */

namespace Aragil\Core;

use Aragil\Dispatcher\Dispatcher;
use Aragil\Request\Request;
use Aragil\Router\Router;

class Application
{
    use ApplicationComponent;

    private $di;

    const MAIN_THREAD_NAME = 'main';
    const HTTP = 'http';
    const CONSOLE = 'console';
    const AVAIABLE_TYPES = [
        self::HTTP, self::CONSOLE
    ];

    private $name;
    private $type;

    public function __construct($name = self::MAIN_THREAD_NAME, $type = self::HTTP)
    {
        $this->name = $name;
        $this->setType($type);
        $this->di = Di::newInstance($name);
        $this->di['app'] = $this;
    }

    public function getType()
    {
        return $this->type;
    }

    private function setType($type)
    {
        if(!in_array($type, self::AVAIABLE_TYPES)) {
            throw new \InvalidArgumentException("Incorrect application type` {$type}");
        }

        if($this->isMainThread()) {
            $type = defined('APP_CONSOLE') ? self::CONSOLE : $type;
        }

        $this->type = $type;
    }

    private function isMainThread()
    {
        return $this->name === self::MAIN_THREAD_NAME;
    }

    /**
     * @return void
     */
    public function start()
    {
        $this->setConfig();
        $this->setRequest();
        $this->setRouter();
        $this->setDispatcher();
    }

    /**
     * @return $this
     */
    private function setConfig()
    {
        $di = $this->getDi();
        $di['config'] = \Aragil\Core\Config::make(false);

        return $this;
    }

    /**
     * @return $this
     */
    private function setDispatcher()
    {
        $di = $this->getDi();
        $di['dispatcher'] = Dispatcher::make($this);

        return $this;
    }

    /**
     * @return $this
     */
    private function setRouter()
    {
        $di = $this->getDi();

        if($this->isMainThread()) {
            $di['router'] = new Router();
            $di['router']->loadRoutes();
        } else {
            $di['router'] = Di::getInstance()['router'];
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function setRequest()
    {
        $di = $this->getDi();
        $di['request'] = Request::make($this);

        return $this;
    }

    /**
     * @return \Aragil\Http\Response
     */
    public function handle()
    {
        try {
            /** @var $dispatcher Dispatcher */
            $dispatcher = $this->getDi()['dispatcher'];
            return $dispatcher->dispatch();
        } catch (\Throwable $throwable) {
            return ErrorHandler::handle($this, $throwable);
        }
    }

    public function terminate()
    {
        $this->getDi()->removeInstance($this->name);
    }
}