<?php


namespace ANDS\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ANDSCommand extends Command
{
    private $input;
    private $output;

    protected function configure()
    {
        parent::configure(); // TODO: Change the autogenerated stub
    }

    public function setUp(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        initEloquent();
        date_default_timezone_set('UTC');
    }

    public function log($message, $wrapper = null)
    {
        if ($wrapper) {
            $this->output->writeln("<$wrapper>$message</$wrapper>");
            return;
        }
        $this->output->writeln($message);
        return;
    }

    public function isQuite()
    {
        return $this->output->isQuite();
    }

    public function isVerbose()
    {
        return $this->output->isVerbose();
    }

    public function isDebug()
    {
        return $this->output->isDebug();
    }
}