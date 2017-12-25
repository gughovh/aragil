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
     * @param Application $app
     * @param \Throwable $throwable
     * @return Response
     * @throws \Throwable
     */
    public static function handle(Application $app, \Throwable $throwable)
    {
        Log::fatal($throwable);

        /** @var $request Request */
        $request = $app->getDi()['request'];
        if(!$request->isHttp()) {
            throw $throwable;
        }

        if($throwable instanceof ApplicationException) {
            return new Response($throwable->getMessage(), $throwable->getCode());
        }

        throw $throwable;
    }
}