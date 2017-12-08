<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-04
 * Time: 6:08 PM
 */

namespace Micro\Core;


use Micro\Dispatcher\Dispatcher;
use Micro\Request\Request;
use Micro\Router\Router;

class Application
{
    private $di;

    public function __construct()
    {
        $this->di = Di::getInstance();
        $this->di['app'] = $this;
    }

    /**
     * @return void
     */
    public function start()
    {
        $this->setConfig();
        $this->setRouter();
        $this->setDispatcher();
        $this->setRequest();
    }

    /**
     * @return $this
     */
    private function setConfig()
    {
        $di = $this->getDi();
        $di['config'] = \Micro\Core\Config::make();

        return $this;
    }

    /**
     * @return $this
     */
    private function setDispatcher()
    {
        $di = $this->getDi();
        $di['dispatcher'] = Dispatcher::make();

        return $this;
    }

    /**
     * @return $this
     */
    private function setRouter()
    {
        $di = $this->getDi();
        $di['router'] = new Router();
        $di['router']->loadRoutes();

        return $this;
    }

    /**
     * @return $this
     */
    private function setRequest()
    {
        $di = $this->getDi();
        $di['request'] = Request::make();

        return $this;
    }

    /**
     * @return \Micro\Http\Response
     */
    public function handle()
    {
        try {
            /** @var $dispatcher Dispatcher */
            $dispatcher = $this->getDi()['dispatcher'];
            return $dispatcher->dispatch();
        } catch (\Throwable $throwable) {
            return ErrorHandler::handle($throwable);
        }
    }

    /**
     * @return Di|null
     */
    public function getDi()
    {
        return $this->di;
    }
}