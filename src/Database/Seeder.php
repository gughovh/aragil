<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-02-01
 * Time: 3:21 PM
 */

namespace Aragil\Database;


interface Seeder
{
    /**
     * @return array|string
     */
    function getDataOrQuery();
}