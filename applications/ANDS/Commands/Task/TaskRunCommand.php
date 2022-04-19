<?php

namespace ANDS\Commands\Task;

use ANDS\Commands\ANDSCommand;
use ANDS\Task\TaskRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TaskRunCommand extends ANDSCommand
{
    protected function configure()
    {
        $this
            ->setName('task:run')
            ->setDescription('Run a Task by Id')
            ->addArgument('id', InputArgument::REQUIRED, 'Task ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $taskID = $input->getArgument("id");

        $task = TaskRepository::getById($taskID);
        $task->run();
    }

}