<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 1:02 PM
 */
declare(ticks = 1);

namespace Aragil\Queue\Worker;

use Aragil\Queue\Drivers\Driver;
use Aragil\Queue\Job\Job;

class Worker
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var array
     */
    private $options;

    /**
     * @var Job
     */
    private $currentJob;

    /**
     * @var array
     */
    private $data;

    public function __construct($options = [])
    {
        $this->driver = Driver::make();
        $this->options = $options;
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
                }
            }

            if($sleep) {
                sleep($sleep);
            }
        }
    }

    private function registerShutDown() :void
    {
        register_shutdown_function(function () {
            $lastError = error_get_last();
            if (!is_null($lastError) && $lastError['type'] === E_ERROR) {
                $this->shutdown(false);
            }
        });
    }

    private function sigintShutdown()
    {
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
        pcntl_signal(SIGHUP, [$this, 'shutdown']);
    }

    private function shutdown($die = true)
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
        $die && die;
    }
}