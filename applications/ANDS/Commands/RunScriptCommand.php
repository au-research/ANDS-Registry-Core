<?php


namespace ANDS\Commands;


use ANDS\Commands\Script\NLAPullBack;
use ANDS\Commands\Script\ProcessGroups;
use ANDS\Commands\Script\ProcessScholix;
use ANDS\Commands\Script\ProcessServiceLinksScript;
use ANDS\Commands\Script\ProcessTitles;
use ANDS\Commands\Script\ReportScript;
use ANDS\Commands\Script\UpdateDataciteClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunScriptCommand extends ANDSCommand
{
    protected $scripts = [
        "processGroups" => ProcessGroups::class,
        "processTitles" => ProcessTitles::class,
        "processScholix" => ProcessScholix::class,
        "updateDataciteClient" => UpdateDataCiteClient::class,
        "report" => ReportScript::class,
        "processServiceLinks" => ProcessServiceLinksScript::class,
        "nlaPullBack" => NLAPullBack::class
    ];

    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Get something from ro')
            ->setHelp("This command allows you to run custom scripts")

            ->addArgument('what', InputArgument::REQUIRED, implode('|', array_keys($this->scripts)))
            ->addOption(
                'params',
                'p',
                InputOption::VALUE_OPTIONAL,
                "custom parameters for scripts",
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $what = $input->getArgument("what");
        if (!in_array($what, array_keys($this->scripts))) {
            $this->log("unknown $what, available: ". implode('|', array_keys($this->scripts)), "error");
            return;
        }

        $script = new $this->scripts[$input->getArgument("what")]($this, $input, $output);
        $script->run();
    }
}