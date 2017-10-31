<?php


namespace ANDS\Commands\Script;


use ANDS\Commands\ANDSCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\Input;

class GenericScript implements GenericScriptRunnable
{
    private $command;
    private $input;
    private $output;

    /**
     * GenericScript constructor.
     * @param $command
     */
    public function __construct(ANDSCommand $command, $input, $output)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
    }

    public function table($rows, $headers = [])
    {
        $table = new Table($this->output);
        $table->setHeaders($headers)
            ->setRows($rows)
            ->render();
    }

    public function progressStart($total)
    {
        $this->progress = new ProgressBar($this->output, $total);
    }

    public function progressAdvance($count)
    {
        $this->progress->advance();
    }

    public function progressFinish()
    {
        $this->progress->finish();
        $this->log("\n");
    }

    public function info($message)
    {
        $this->command->log($message, "info");
    }

    public function error($message)
    {
        $this->log("\n");
        $this->command->log($message, "error");
        $this->log("\n");
    }

    public function debug($message)
    {
        if ($this->command->isDebug()) {
            $this->command->log("[DEBUG] ". $message, "info");
        }
    }

    public function log($message, $wrapper = null)
    {
        $this->command->log($message, $wrapper);
    }

    /**
     * @return Input
     */
    public function getInput()
    {
        return $this->input;
    }

    public function run()
    {
        // TODO: Implement run() method.
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }
}

interface GenericScriptRunnable {
    public function run();
}