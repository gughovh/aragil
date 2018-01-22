<?php
/**
 * Created by IntelliJ IDEA.
 * User: gurgen
 * Date: 2018-01-22
 * Time: 11:53 AM
 */

namespace Aragil\Queue\Job;

use ReflectionProperty;

abstract class Job implements ShouldQueue
{
    protected $queue;

    public function serialize() :string
    {
        $r = new \ReflectionClass($this);
        $props = $r->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
        $data = [];

        foreach ($props as $prop) {
            $propName = $prop->getName();
            $data[$propName] = $this->{$propName};
        }

        return serialize($data);
    }

    public function unserialize($serialized) :void
    {
        $data = unserialize($serialized);

        foreach ($data as $propName => $value) {
            $this->{$propName} = $value;
        }
    }

    public function setQueue(string $queue)
    {
        $this->queue = $queue;
    }

    public function getQueue() :string
    {
        return (string)$this->queue;
    }

    abstract public function handle():void;
}