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
            ->addOption('log-path', null, InputOption::VALUE_OPTIONAL)
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

        // give the worker a logger
        $logPath = $input->getOption('log-path');
        if ($logPath) {
            $logger = $this->getLogger($name, $logPath);
            $logger->pushHandler(new StreamHandler("php://stdout"));
            $worker->setLogger($logger);
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

    /**
     * Determine a Logger instance depends on the logPath is a directory or a file
     * @param $name
     * @param $logPath
     * @return \Monolog\Logger|\Psr\Log\LoggerInterface|null
     */
    private function getLogger($name, $logPath)
    {

        // default is a dir path
        $dirPath = $logPath;
        $fileName = "worker.$name.log";

        // if logPath is to a file
        if (!is_dir($logPath)) {
            $dirPath = dirname($logPath);
            $fileName = basename($logPath);
        }

        return Log::createDriver("worker.logger.$name", [
            'driver' => 'single',
            'path' => $dirPath,
            'file' => $fileName,
            'level' => 'debug'
        ]);

    }
}