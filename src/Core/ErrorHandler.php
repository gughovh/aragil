<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 3:29 PM
 */

namespace Aragil\Core;


use Aragil\Exceptions\ApplicationException;
use Aragil\Helpers\Log;
use Aragil\Http\Response;
use Aragil\Request\Request;

class ErrorHandler
{
    /**
     * @param \Throwable $throwable
     * @return Response
     * @throws \Throwable
     */
    public static function handle(\Throwable $throwable)
    {
        Log::fatal($throwable);

        if(!Request::isHttp()) {
            throw $throwable;
        }

        if($throwable instanceof ApplicationException) {
            return new Response($throwable->getMessage(), $throwable->getCode());
        }

        throw $throwable;
    }
}