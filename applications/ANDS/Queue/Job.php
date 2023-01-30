<?php

namespace ANDS\Queue;

abstract class Job
{
    abstract function init(array $payload);
    abstract function run();
    abstract function toArray();

    function toJson() {
        return json_encode([
            'class' => get_class($this),
            'payload' => $this->toArray()
        ]);
    }

    public function __toString()
    {
        return "Job[class=" . get_class($this) . "]";
    }


    /**
     * @param $raw
     * @return \ANDS\Queue\Job
     */
    public static function getJobInstance($raw) {
        $data = json_decode($raw, true);
        $jobClass = $data['class'];
        $jobPayload = $data['payload'];
        $job = new $jobClass;
        $job->init($jobPayload);
        return $job;
    }
}