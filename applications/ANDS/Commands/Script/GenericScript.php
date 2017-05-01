<?php


namespace ANDS\Commands\Script;


use Symfony\Component\Console\Input\Input;

class GenericScript implements GenericScriptRunnable
{
    private $command;
    private $input;

    /**
     * GenericScript constructor.
     * @param $command
     */
    public function __construct($command, $input)
    {
        $this->command = $command;
        $this->input = $input;
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
}

interface GenericScriptRunnable {
    public function run();
}