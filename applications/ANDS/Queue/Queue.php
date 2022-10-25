<?php

namespace ANDS\Queue;

use ANDS\Util\Config;

abstract class Queue
{
    protected $name;

    abstract function enqueue(Job $job);
    abstract function dequeue();
    abstract function size();
    abstract function get($limit, $offset);
    abstract function flush();

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public static function make($options) {
        if ($options['driver']=="redis") {
            return new RedisQueue($options);
        }

        return null;
    }
}