<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 25.12.2017
 * Time: 18:18
 */

namespace Aragil\Core;


trait ApplicationComponent
{
    private function setDi(Di $di)
    {
        $this->di = $di;
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }
}