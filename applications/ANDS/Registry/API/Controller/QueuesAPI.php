<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Queue\Job;
use ANDS\Queue\QueueService;
use ANDS\Registry\API\Request;

class QueuesAPI
{
    /**
     * Get all available Queues ID
     *
     * serves GET /api/registry/queues/
     * @return array
     */
    public function index()
    {
        $queues = QueueService::queues();
        return collect($queues)->keys()->toArray();
    }

    /**
     * Get a queue by ID
     *
     * Displays it's id, name and size
     * serves GET /api/registry/queues/{queueID}
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function show($id)
    {
        $queue = QueueService::getQueue($id);
        if (!$queue) {
            throw new \Exception("Queue[id=$id] not found");
        }
        return [
            'id' => $id,
            'name' => $queue->getName(),
            'size' => $queue->size()
        ];
    }

    /**
     * Shows a list of Job that is in this queue
     *
     * @param $id
     * @return array
     * @throws \Exception
     */
    public function jobs($id)
    {
        $queue = QueueService::getQueue($id);
        if (!$queue) {
            throw new \Exception("Queue[id=$id] not found");
        }
        $limit = Request::value('limit', 100);
        $offset = Request::value('offset', 0);
        $jobs = $queue->get($limit, $offset);
        return collect($jobs)->map(function ($job){
            return json_decode(Job::getJobInstance($job)->toJson());
        })->toArray();
    }
}