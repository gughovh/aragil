<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 11:43 AM
 */

namespace Aragil\Queue\Drivers;

use Aragil\Queue\Job\Job;
use Aragil\Storage\Redis;

class RedisDriver extends Driver
{
    private $redisConnection;

    public function __construct()
    {
        $this->redisConnection = Redis::client();
    }

    public function addJob(Job $job) :void
    {
        $this->redisConnection->zincrby($this->getFreshKey($job->getQueue()), 1, serialize($job));
    }

    public function getJob($queue = null) :?Job
    {
        if ($freshKey = $this->getFreshKey($queue)) {
            $job = current($this->redisConnection->zrange($freshKey, -1, -1));
            $this->redisConnection->zrem($freshKey, $job);
            $jobObj = unserialize($job);

            if($jobObj instanceof Job) {
                $this->redisConnection->hset(
                    $this->getInWorkKey(
                        $this->getQueueFromKey($freshKey)
                    ),
                    $job,
                    true
                );
                return $jobObj;
            }
        }

        return null;
    }

    public function failJob(Job $job) :void
    {
        $queue = $job->getQueue();
        $this->redisConnection->zincrby($this->getFailedKey($queue), 1, serialize($job));
        $this->expireJob($job);
    }

    public function expireJob(Job $job) :void
    {
        $this->redisConnection->hdel($this->getInWorkKey($job->getQueue()), serialize($job));
    }

    public function getFailedCount($queue = null) :array
    {
        $counts = [];
        $failedKeys = $this->redisConnection->keys($this->getFailedKey($queue ?? '*'));

        foreach ($failedKeys as $key) {
            $counts[$this->getQueueFromKey($key)] = $this->redisConnection->zcount($key, '-inf', '+inf');
        }

        return $counts;
    }

    public function getFreshCount($queue = null) :array
    {
        $counts = [];
        $freshKeys = array_filter($this->redisConnection->keys($this->getFreshKey($queue ?? '*')), function ($key) {
            return !array_intersect([self::QUEUE_IN_WORK_PREFIX, self::QUEUE_FAILED_PREFIX], explode(self::DELIMITER, $key));
        });

        foreach ($freshKeys as $key) {
            if(!array_intersect([self::QUEUE_IN_WORK_PREFIX, self::QUEUE_FAILED_PREFIX], explode(self::DELIMITER, $key))) {
                $counts[$this->getQueueFromKey($key)] = $this->redisConnection->zcount($key, '-inf', '+inf');
            }
        }

        return $counts;
    }

    public function getInWorkCount($queue = null) :array
    {
        $counts = [];
        $inWorkKeys = $this->redisConnection->keys($this->getInWorkKey($queue ?? '*'));

        foreach ($inWorkKeys as $key) {
            $counts[$this->getQueueFromKey($key)] = $this->redisConnection->hlen($key);
        }

        return $counts;
    }

    public function setWorkerData(array $data) :void
    {
        $this->redisConnection->set(self::QUEUE_WORKER_KEY, json_encode(array_merge($this->getWorkerData(), $data)));
    }

    public function getWorkerData(): array
    {
        return json_decode($this->redisConnection->get(self::QUEUE_WORKER_KEY), true) ?? [];
    }

    protected function getKey($prefix, $queue = null, $suffix = null) :?string
    {
        $prefix = (array)$prefix;
        $suffix = (array)$suffix;
        $queueKey = join(self::DELIMITER, $prefix)
            . self::DELIMITER
            . $queue
            . ($suffix ? self::DELIMITER . join(self::DELIMITER, $suffix) : '');

        if(!$queue) {
            $pc = count($prefix);
            $sc = count($suffix);
            $queueKey = join(self::DELIMITER, $prefix) . self::DELIMITER . '*' . join(self::DELIMITER, $suffix);
            $keys = $this->redisConnection->keys($queueKey);

            foreach ($keys as $key) {
                $keyItems = explode(self::DELIMITER, $key);
                if(count($keyItems) == 1 + $pc + $sc
                    && array_slice($keyItems, 0, $pc) == $prefix
                    && (!$sc || array_slice($keyItems, -$sc) == $suffix)
                ) {

                    return $key;
                }
            }

            return null;
        }

        return $queueKey;
    }
}