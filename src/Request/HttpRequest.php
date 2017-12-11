<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2017-12-06
 * Time: 2:58 PM
 */

namespace Aragil\Request;


class HttpRequest extends Request
{
    private $queryParams;
    private $formParams;

    protected function init()
    {
//        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->queryParams = $_GET;
        $this->formParams = $_POST;
    }

    /**
     * @return array
     */
    protected function parsePathInfo()
    {
        return array_values(
            array_filter(
                explode('/', str_replace($_SERVER['SCRIPT_NAME'],'', $_SERVER['REQUEST_URI']))
            )
        );
    }
}