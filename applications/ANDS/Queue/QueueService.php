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
     * @return \ANDS\Queue\Queue
     */
    public static function getQueue($id = null) {
        if ($id && array_key_exists($id, self::$queues)) {
            return self::$queues['id'];
        }

        return self::$queues[self::$defaultQueue];
    }
}