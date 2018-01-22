<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 11:30 AM
 */

namespace Aragil\Console\Commands\Queue;


use Aragil\Console\Command;
use Aragil\Queue\Drivers\Driver;
use Aragil\Queue\Worker\Worker;

class QueueInWork extends Command
{
    protected $description = 'Shows queues working jobs count';

    public function handle()
    {
        $driver = Driver::make();
        $failedJobsCount = $driver->getFreshCount();

        if(empty($failedJobsCount)) {
            $this->line('No working jobs');
            return;
        }

        $this->line("In work jobs counts`");
        foreach ($failedJobsCount as $queue => $count) {
            $this->line("\t{$queue}: {$count}");
        }
    }
}