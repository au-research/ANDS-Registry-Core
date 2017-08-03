<?php


namespace ANDS\Commands\Script;


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
    public function __construct($command, $input, $output)
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