<?php

namespace ANDS\Queue;

use ANDS\Log\Log;
use ANDS\Registry\Events\EventServiceProvider;
use Monolog\Handler\NullHandler;
use Monolog\Logger as Monolog;

class QueueWorker
{
    /** @var \ANDS\Queue\Queue */
    protected $queue;

    protected $name;

    protected $daemon = false;

    protected $sleep = 3;

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
        $this->logger = new Monolog("worker.logger.$this->name");
        $this->logger->pushHandler(new NullHandler());
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

        $this->logger->info("Worker Started", ['worker' => $this->name, 'queue' => $this->queue->getName()]);

        while($this->shouldContinue()) {
            $job = $this->getNextJob();

            // execute the job if there's a job found
            if ($job) {
                $this->logger->info("Running Job", ['worker' => $this->name, 'job' => (string) $job]);
                try {
                    $job->run();
                } catch (\Exception $e) {
                    $this->logger->error("Job Failed", [
                            'worker' => $this->name,
                            'job' => (string)$job,
                            'exception' => [
                                'message' => $e->getMessage()
                            ]
                        ]
                    );
                }
            } else {
                // by default, the worker will keep processing jobs until it's finished
                // when the queue is empty, it will sleep between each poll by the configured amount
                sleep($this->getSleep());
            }
        }

        $this->logger->info("Worker {$this->name} stopped");
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

    /**
     * @return int
     */
    public function getSleep()
    {
        return $this->sleep;
    }

    /**
     * @param int $sleep
     */
    public function setSleep($sleep)
    {
        $this->sleep = $sleep;
    }
}