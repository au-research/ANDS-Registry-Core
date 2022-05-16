<?php

namespace ANDS\Commands\Queue;

use ANDS\Commands\ANDSCommand;
use ANDS\Log\Log;
use ANDS\Queue\QueueService;
use ANDS\Queue\QueueWorker;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger as Monolog;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueWorkCommand extends ANDSCommand
{
    protected function configure()
    {
        /**
         * php ands.php queue:work --queue={} --name={} --log_path={} --daemon
         */
        $this
            ->setName('queue:work')
            ->setDescription('Work the Queue')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL)
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL)
            ->addOption('daemon', null, InputOption::VALUE_NONE, null)
            ->addOption('log_path', null, InputOption::VALUE_OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        QueueService::init();
        $queue = $input->getOption('queue') ?: QueueService::getQueue();
        $name = $input->getOption('name') ?: uniqid();
        $daemon = $input->getOption('daemon');
        $worker = new QueueWorker($queue, $name, $daemon);

        $logPath = $input->getOption('log_path');
        if ($logPath) {
            $logger = Log::createDriver("worker.logger.$name", [
                'driver' => 'single',
                'path' => $logPath,
                'file' => "worker.$name.log",
                'level' => 'debug'
            ]);

            if ($logger) {
                $logger->pushHandler(new StreamHandler("php://stdout"));
                $worker->setLogger($logger);
            }
        }

        // catch catchable termination signals
        declare(ticks = 1);
        $signals = [SIGINT, SIGTERM, SIGHUP, SIGUSR1, SIGTERM];
        foreach ($signals as $signal) {
            pcntl_signal($signal, function($code) use ($worker){
                $worker->getLogger()->error("Worker[name={$worker->getName()}] stopped with Signal[code=$code]");
                exit;
            });
        }

        // catch fatal error
        register_shutdown_function( function() use ($worker) {
            $error = error_get_last();
            if ($error) {
                $worker->getLogger()->error("Worker[name={$worker->getName()}] stopped with Fatal Error[message={$error['message']}", $error);
            }
        });

        $worker->work();
    }
}