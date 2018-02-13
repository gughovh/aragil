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

class QueueCount extends Command
{
    protected $description = 'Shows queues jobs count';

    public function handle()
    {
        $driver = Driver::make();
        $freshJobsCount = $driver->getFreshCount();

        if(empty($freshJobsCount)) {
            $this->line('No jobs', false);
            return;
        }

        $this->line("Jobs counts`");
        foreach ($freshJobsCount as $queue => $count) {
            $this->line("\t{$queue}: {$count}", false);
        }
    }
}