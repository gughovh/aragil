<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 1:02 PM
 */
declare(ticks = 1);

namespace Aragil\Queue\Worker;

use Aragil\Helpers\Log;
use Aragil\Queue\Drivers\Driver;
use Aragil\Queue\Job\Job;
use Aragil\Console\Command;

class Worker
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var QueueWork
     */
    private $command;

    /**
     * @var array
     */
    private $options = [
        'queue' => null,
        'timeout' => 600,
        'sleep' => 0,
        'retries' => 5,
    ];

    /**
     * @var Job
     */
    private $currentJob;

    /**
     * @var array
     */
    private $data;

    public function __construct(Command $command, array $options = [])
    {
        $this->driver = Driver::make();
        $this->options = array_merge($this->options, $options);
        $this->command = $command;
    }

    public function run() :void
    {
        $queue = $this->options['queue'];
        $timeout = $this->options['timeout'];
        $sleep = $this->options['sleep'];
        $retries = $this->options['retries'];
        $this->driver = $driver = Driver::make();
        $this->data = $driver->getWorkerData();

        $this->registerShutDown();
        $this->sigintShutdown();

        while (true) {
            if($timeout) {
                set_time_limit($timeout);
            }

            $job = $this->currentJob = $driver->getJob($queue);

            if(!($job instanceof Job)) {
                sleep(3);
                continue;
            }

            $job->setWorker($this);
            $dKey = serialize($job);
            $this->data[$dKey] = $this->data[$dKey] ?? ['tries' => 0];

            try {
                $this->data[$dKey]['tries']++;
                $job->handle();
                $driver->expireJob($job);
                $this->currentJob = null;
                unset($this->data[$dKey]);
            } catch (\Throwable $e) {
                if($this->data[$dKey] > $retries) {
                    $driver->failJob($job);
                } else {
                    $driver->addJob($job);
                    $driver->expireJob($job);
                }

                Log::fatal($e);
            }

            if($sleep) {
                sleep($sleep);
            }
        }
    }

    public function getCommand()
    {
        return $this->command;
    }

    private function registerShutDown() :void
    {
        register_shutdown_function(function () {
            $lastError = error_get_last();
            $fatalErrors = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];
            if (!is_null($lastError) && in_array($lastError['type'], $fatalErrors)) {
                $this->shutdown(false, new \ErrorException(
                    $lastError['message'], $lastError['type'], 0, $lastError['file'], $lastError['line']
                ));
            }
        });
    }

    private function sigintShutdown()
    {
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
        pcntl_signal(SIGHUP, [$this, 'shutdown']);
    }

    private function shutdown($die = true, $error = null)
    {
        if($this->currentJob) {
            $dKey = serialize($this->currentJob);
            if($this->data[$dKey]['tries'] > $this->options['retries']) {
                $this->driver->failJob($this->currentJob);
                unset($this->data[$dKey]);
            } else {
                $this->driver->addJob($this->currentJob);
                $this->driver->expireJob($this->currentJob);
            }

            $this->driver->setWorkerData($this->data);
        }

        if (!($error instanceof \Throwable)) {
            $error = new \ErrorException("Received kill signal. Queue worker, queue - \"{$this->options['queue']}\"");
        }

        Log::fatal($error);

        $die && die;
    }
}