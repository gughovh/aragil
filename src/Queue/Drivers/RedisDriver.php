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
        if ($freshKey = current($this->redisConnection->keys($this->getFreshKey($queue ?? '*')))) {
            $job = $this->redisConnection->lpop($freshKey);
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
                $key = $this->{$options['queueName']}($queue);
                $exists = $this->redisConnection->llen($key);
            }
        }

        if($undefinedType) {
            throw new InvalidArgumentException("Invalid job type - {$jobStatus}");
        }

        return $exists;
    }

    public function getFailedCount($queue = null): array
    {
        $counts = [];
        $failedKeys = $this->redisConnection->keys($this->getFailedKey($queue ?? '*'));

        foreach ($failedKeys as $key) {
            $counts[$this->getQueueFromKey($key)] = $this->redisConnection->llen($key);
        }

        return $counts;
    }

    public function getFreshCount($queue = null): array
    {
        $counts = [];
        $freshKeys = $this->redisConnection->keys($this->getFreshKey($queue ?? '*'));

        foreach ($freshKeys as $key) {
            $counts[$this->getQueueFromKey($key)] = $this->redisConnection->llen($key);
        }

        return $counts;
    }

    public function getInWorkCount($queue = null): array
    {
        $counts = [];
        $inWorkKeys = $this->redisConnection->keys($this->getInWorkKey($queue ?? '*'));

        foreach ($inWorkKeys as $key) {
            $counts[$this->getQueueFromKey($key)] = $this->redisConnection->llen($key);
        }

        return $counts;
    }

    public function setWorkerData(array $data): void
    {
        $this->redisConnection->set(self::QUEUE_WORKER_KEY, json_encode(array_merge($this->getWorkerData(), $data)));
    }

    public function getWorkerData(): array
    {
        return json_decode($this->redisConnection->get(self::QUEUE_WORKER_KEY), true);
    }
}