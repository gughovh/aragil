<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-05
 * Time: 3:56 PM
 */

namespace Micro\Exceptions;


use Throwable;

class NotFoundException extends ApplicationException
{
    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}