<?php

namespace ANDS\Queue;

use Predis\Client as PredisClient;

class RedisQueue extends Queue
{
    protected $client = null;

    public function __construct($options)
    {
        $this->client = new PredisClient($options['url']);
        $this->name = $options['name'];
    }

    function enqueue(Job $job)
    {
        $json = $job->toJson();
        $this->client->rpush($this->name, $json);
    }

    function dequeue()
    {
        try {
            return $this->client->lpop($this->name);
        } catch (\Exception $e) {
            return null;
        }
    }

    function size()
    {
        return $this->client->llen($this->name);
    }

    function get($limit, $offset)
    {
        // todo convert proper limit offset to index range
        return $this->client->lrange($this->name, $offset, $limit);
    }

    function flush()
    {
        $this->client->del($this->name);
    }
}