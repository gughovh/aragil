<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 11:47 AM
 */

namespace Aragil\Queue\Job;


interface ShouldQueue extends \Serializable
{
    public function handle() :void;
}