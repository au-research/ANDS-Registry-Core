<?php


namespace ANDS\Commands\Script;


use ANDS\Commands\ANDSCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\OutputInterface;

class GenericScript implements GenericScriptRunnable
{
    private $command;
    private $input;
    private $output;

    /**
     * GenericScript constructor.
     * @param $command
     */
    public function __construct(ANDSCommand $command)
    {
        $this->command = $command;
        $this->input = $command->getInput();
        $this->output = $command->getOutput();
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
        if ($this->output) {
            $this->progress = new ProgressBar($this->output, $total);
            return;
        }

        $this->log("Progress start: $total");
    }

    public function progressAdvance($count)
    {
        if ($this->output) {
            $this->progress->advance();
            return;
        }

        $this->log("Progress $count");
    }

    public function progressFinish()
    {
        if ($this->output) {
            $this->progress->finish();
            return;
        }
        $this->log("Progress Finished");
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
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return ANDSCommand
     */
    public function getCommand()
    {
        return $this->command;
    }
}

interface GenericScriptRunnable {
    public function run();
}