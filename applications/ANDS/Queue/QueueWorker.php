<?php

namespace ANDS\Queue;

use ANDS\Log\Log;
use ANDS\Registry\Events\EventServiceProvider;

class QueueWorker
{
    /** @var \ANDS\Queue\Queue */
    protected $queue;

    protected $name;

    protected $daemon = false;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * @param $queue
     * @param null $name
     * @param bool $daemon
     */
    public function __construct($queue, $name = null, $daemon = false)
    {
        $this->queue = $queue;
        $this->name = $name ?: uniqid();
        $this->setDaemon($daemon);
    }

    /**
     * Worker in string format for logging purpose
     *
     * @return string
     */
    public function __toString()
    {
        return "Worker[name=$this->name, queue={$this->queue->getName()}, daemon={$this->daemon}]";
    }

    /**
     * Primary function for the worker to work continuously on the configured queue
     *
     * @return void
     */
    public function work() {

        Log::info("Worker {$this->name} started");
        $this->logger->info("Worker {$this->name} started");

        while($this->shouldContinue()) {
            $job = $this->getNextJob();
            if ($job) {
                $this->logger->info("Worker[name={$this->name}] running Job $job");
                $job->run();
            }
            sleep(1);
        }

        $this->logger->info("Worker {$this->name} stopped");
        Log::info("Worker[name={$this->name}] stopped");
    }

    /**
     * Whether the worker should continue
     * @return bool
     */
    private function shouldContinue() {

        // if it's a daemon, it never stops running
        if ($this->daemon) {
            return true;
        }

        // if the queue is empty, then stop
        if ($this->queue->size() === 0) {
            return false;
        }

        return true;
    }

    /**
     * @return \ANDS\Queue\Job|null
     */
    public function getNextJob() {
        $raw = $this->queue->dequeue();
        if ($raw === null) {
            return null;
        }

        return Job::getJobInstance($raw);
    }



    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isDaemon()
    {
        return $this->daemon;
    }

    /**
     * @param bool $daemon
     */
    public function setDaemon($daemon)
    {
        $this->daemon = $daemon;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}