<?php

namespace ANDS\Queue;

use ANDS\Util\Config;

class QueueService
{
    protected static $queues = [];

    protected static $defaultQueue = "default";

    public static function init($config = null) {
        if (!$config) {
            $config = Config::get('queue');
        }

        foreach ($config['connections'] as $id=>$options) {
            self::$queues[$id] = Queue::make($options);
        }
        self::$defaultQueue = $config['default'];
    }

    public static function push(Job $job, $queueId = null) {
        $queue = self::getQueue($queueId);

        $queue->enqueue($job);
    }

    /**
     * Getter for the queue
     *
     * @return \ANDS\Queue\Queue
     */
    public static function getQueue($id = null) {

        // if id is not provided then return the default queue
        if ($id === null) {
            return self::$queues[self::$defaultQueue];
        }

        // if id is provided then return the associated queue
        if ($id && array_key_exists($id, self::$queues)) {
            return self::$queues[$id];
        }

        // last resort: return queue if the name matches the id
        foreach (self::$queues as $queue) {
            if ($queue->getName() === $id) {
                return $queue;
            }
        }

        return null;
    }

    /**
     * Getters for the current queues
     *
     * @return array
     */
    public static function queues() {
        return self::$queues;
    }
}