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

class RequeueFailedJobs extends Command
{
    protected $description = 'Requeue failed jobs. Usage [-q = queue name].';

    public function handle()
    {
        $driver = Driver::make();
        $queues = [$this->options('-q')] ?? $driver->getFailedQueues();

        foreach ($queues as $queue) {
            while ($job = $driver->getFailedJob($queue)) {
                $job->setQueue($queue);
                $driver->addJob($job);
            }
        }
    }
}