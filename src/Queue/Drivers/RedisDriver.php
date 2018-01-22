<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 11:43 AM
 */

namespace Aragil\Queue\Drivers;

use InvalidArgumentException;

class RedisDriver extends Driver
{
    private $redisConnection;

    public function __construct()
    {
        $this->redisConnection = \Aragil\Storage\Redis::client();
    }

    public function addJob(\Aragil\Queue\Job\Job $job) :void
    {
        $this->redisConnection->lpush($this->getFreshKey($job->getQueue()), [serialize($job)]);
    }

    public function getJob($queue = null) :\Aragil\Queue\Job\Job
    {
        if ($queue = current($this->redisConnection->keys($this->getFreshKey($queue ?? '*')))) {
            $job = $this->redisConnection->lpop($queue);
            $this->redisConnection->lpush($this->getInWorkKey($queue), [$job]);

            return unserialize($job);
        }

        return null;
    }

    public function failJob(\Aragil\Queue\Job\Job $job): void
    {
        $queue = $job->getQueue();
        $this->redisConnection->lpush($this->getFailedKey($queue), [serialize($job)]);
        $this->expireJob($job, false);
    }

    public function expireJob(\Aragil\Queue\Job\Job $job, int $jobStatus = self::JOB_STATUS_FRESH & self::JOB_STATUS_WORK & self::JOB_STATUS_FAILED): void
    {
        $queue = $job->getQueue();
        $expireOptions = [
            [
                'queue' => $this->getFreshKey($queue),
                'status' => self::JOB_STATUS_FRESH
            ],
            [
                'queue' => $this->getInWorkKey($queue),
                'status' => self::JOB_STATUS_WORK
            ],
            [
                'queue' => $this->getInWorkKey($queue),
                'status' => self::JOB_STATUS_FAILED
            ],
        ];

        foreach ($expireOptions as $option) {
            if (($jobStatus & $option['status']) && $this->hasJob($option['queue'], $option['status'])) {
                $this->redisConnection->lpop($option['queue']);
            }
        }
    }

    public function hasJob($queue = null, int $jobStatus = self::JOB_STATUS_FRESH): bool
    {
        $undefinedType = true;
        $exists = false;

        foreach (self::JOB_TYPES as $typeName => $options) {
            if($options['type'] & $jobStatus) {
                $undefinedType = false;
                $queue = $this->{$options['queueName']}();
                $exists = $this->redisConnection->llen($queue);
            }
        }

        if($undefinedType) {
            throw new InvalidArgumentException("Invalid job type - {$jobStatus}");
        }

        return $exists;
    }
}