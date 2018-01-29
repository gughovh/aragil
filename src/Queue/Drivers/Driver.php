<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 11:43 AM
 */

namespace Aragil\Queue\Drivers;

use Aragil\Queue\Job\Job;

abstract class Driver
{
    const DELIMITER = ':';
    const QUEUE_PREFIX = 'aragil-queue';
    const QUEUE_FAILED_PREFIX = 'failed';
    const QUEUE_IN_WORK_PREFIX = 'in-work';

    const QUEUE_WORKER_KEY = 'queue-worker-data';

    const JOB_STATUS_FRESH  = 1;
    const JOB_STATUS_WORK   = 2;
    const JOB_STATUS_FAILED = 4;

    const JOB_TYPES = [
        'fresh' => [
            'type' => self::JOB_STATUS_FRESH,
            'queueName' => 'getFreshKey',
        ],
        'in-work' => [
            'type' => self::JOB_STATUS_WORK,
            'queueName' => 'getInWorkKey',
        ],
        'failed' => [
            'type' => self::JOB_STATUS_FAILED,
            'queueName' => 'getFailedKey',
        ],
    ];

    const DEFAULT_INSTANCE = 'redis';

    const INSTANCE_TYPES = [
        'redis' => RedisDriver::class
    ];

    /**
     * @return self
     */
    public static function make()
    {
        $class = self::INSTANCE_TYPES[config('queue.driver') ?? self::DEFAULT_INSTANCE] ?? self::INSTANCE_TYPES[self::DEFAULT_INSTANCE];
        return new $class;
    }

    protected function getFreshKey($queue) :string
    {
        return self::QUEUE_PREFIX . self::DELIMITER . $queue;
    }

    protected function getInWorkKey($queue) :string
    {
        return self::QUEUE_PREFIX . self::DELIMITER . $queue . self::DELIMITER . self::QUEUE_IN_WORK_PREFIX;
    }

    protected function getFailedKey($queue) :string
    {
        return self::QUEUE_PREFIX . self::DELIMITER . $queue . self::DELIMITER . self::QUEUE_FAILED_PREFIX;
    }

    protected function getQueueFromKey(string $key) :string
    {
        $searches = [
            self::QUEUE_PREFIX . self::DELIMITER,
            self::DELIMITER . self::QUEUE_IN_WORK_PREFIX,
            self::DELIMITER . self::QUEUE_FAILED_PREFIX
        ];
        return str_replace($searches, '', $key);
    }

    abstract public function addJob(Job $job);

    abstract public function getJob($queue = null) :?Job;
    abstract public function getFailedJob($queue = null) :?Job;

    abstract public function getFailedQueues();

    abstract public function expireJob(Job $job) :void;
    abstract public function failJob(Job $job) :void;

    abstract public function getFailedCount($queue = null) :array;
    abstract public function getFreshCount($queue = null) :array;
    abstract public function getInWorkCount($queue = null) :array;

    abstract public function setWorkerData(array $data) :void;
    abstract public function getWorkerData() :array;
}