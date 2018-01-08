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
    private static $shutdownRegistreted = false;

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

    public static function registerShutdownHandler()
    {
        if(self::$shutdownRegistreted) {
            return;
        }

        register_shutdown_function(function () {
            $fatalErrors = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];
            if (!is_null($error = error_get_last()) && in_array($error['type'], $fatalErrors)) {
                Log::fatal(new \ErrorException(
                    $error['message'], $error['type'], 0, $error['file'], $error['line'], 0
                ));
            }
        });

        self::$shutdownRegistreted = true;
    }
}