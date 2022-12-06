<?php

namespace ANDS\Commands\Task;

use ANDS\Commands\ANDSCommand;
use ANDS\Task\TaskRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TaskStopCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('task:stop')
            ->setDescription('Stop a Task by Id')
            ->addArgument('id', InputArgument::REQUIRED, 'Task ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $taskID = $input->getArgument("id");

        $task = TaskRepository::getById($taskID);
        $task->stop();
    }
}