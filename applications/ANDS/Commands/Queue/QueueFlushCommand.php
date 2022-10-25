<?php

namespace ANDS\Commands\Queue;

use ANDS\Commands\ANDSCommand;
use ANDS\Queue\QueueService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueFlushCommand extends ANDSCommand
{
    protected function configure()
    {
        /**
         * Usage:
         * php ands.php queue:flush --queue-id
         */
        $this
            ->setName('queue:flush')
            ->addOption('queue-id', null, InputOption::VALUE_OPTIONAL, 'Queue ID to flush', null)
            ->setDescription('List all Queues')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        QueueService::init();

        $queue = QueueService::getQueue($input->getOption('queue-id'));
        $queue->flush();
        $this->log("Flushed queue: {$queue->getName()}");
    }
}