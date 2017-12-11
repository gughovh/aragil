<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 11.12.2017
 * Time: 14:32
 */

namespace Aragil\Http;


use Aragil\Core\Di;
use Aragil\Request\HttpRequest;

abstract class Controller
{
    /**
     * @var null|Di
     */
    private $di = null;

    /**
     * @return Di
     */
    public function di()
    {
        if (is_null($this->di)) {
            $this->di = Di::getInstance();
        }

        return $this->di;
    }

    /**
     * @return HttpRequest
     */
    public function request()
    {
        return $this->di()['request'];
    }
}