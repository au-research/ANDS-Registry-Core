<?php

namespace ANDS\Commands\Queue;

use ANDS\Commands\ANDSCommand;
use ANDS\Queue\QueueService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueListCommand extends ANDSCommand
{
    protected function configure()
    {
        /**
         * Usage:
         * php ands.php queue:list
         */
        $this
            ->setName('queue:list')
            ->setDescription('List all Queues')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        QueueService::init();
        $queues = QueueService::queues();

        $rows = [];
        foreach ($queues as $queueId=>$queue) {
            $rows[] = [$queueId, $queue->getName(), $queue->size()];
        }

        $this->table($rows, $headers = ['Queue ID', 'Queue Name', 'Total']);
    }
}