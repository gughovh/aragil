<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 11:30 AM
 */

namespace Aragil\Console\Commands\Queue;


use Aragil\Console\Command;
use Aragil\Queue\Worker\Worker;

class QueueInWork extends Command
{
    public function handle()
    {
        $driver = \Driver::make();
        $this->line("In work jobs count: {$driver->getInWorkCount()}");
    }
}