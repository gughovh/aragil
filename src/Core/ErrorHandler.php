<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 3:29 PM
 */

namespace Micro\Core;


use Micro\Exceptions\ApplicationException;
use Micro\Http\Response;
use Micro\Request\Request;

class ErrorHandler
{
    /**
     * @param \Throwable $throwable
     * @return Response
     * @throws \Throwable
     */
    public static function handle(\Throwable $throwable)
    {
        if(!Request::isHttp()) {
            throw $throwable;
        }

        if($throwable instanceof ApplicationException) {
            return new Response($throwable->getMessage(), $throwable->getCode());
        }

        throw $throwable;
    }
}