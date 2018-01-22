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

class QueueWork extends Command
{
    protected $description = 'Run queue jobs. Usage [-q = queue name, -t = timeout, -s = sleep, -r = retry count].';

    public function handle()
    {
        $defaultOptions = config('queue');
        $options = [
            'queue' => $this->options('-q'),
            'timeout' => $this->options('-t') ?? $defaultOptions['timeout'],
            'sleep' => $this->options('-s') ?? $defaultOptions['sleep'],
            'retries' => $this->options('-r') ?? $defaultOptions['retries'],
        ];

        $worker = new Worker($options);
        $worker->run();
    }
}