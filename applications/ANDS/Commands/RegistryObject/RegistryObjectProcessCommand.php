<?php


namespace ANDS\Commands\RegistryObject;


use ANDS\Commands\ANDSCommand;
use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
use ANDS\Registry\Providers\LinkProvider;
use ANDS\Registry\Providers\Quality\QualityMetadataProvider;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\SubjectProvider;
use ANDS\Registry\Providers\Scholix\ScholixProvider;
use ANDS\Registry\Providers\RIFCS\TitleProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ReflectionMethod;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegistryObjectProcessCommand extends ANDSCommand
{
    protected $processors = [
        'scholix' => ScholixProvider::class,
        'subject' => SubjectProvider::class,
        'quality' => QualityMetadataProvider::class,
        'links' => LinkProvider::class,
        'title' => TitleProvider::class,
        'core' => CoreMetadataProvider::class,
        'date' => DatesProvider::class,
        'dci' => DataCitationIndexProvider::class
    ];

    protected function configure()
    {
        // load the constants needed
        define("BASEPATH", './');
        require(BASEPATH. 'engine/config/constants.php');

        $this
            ->setName('ro:process')
            ->setDescription('Get something from ro')
            ->setHelp("This command allows you to run provider:process on keys")

            ->addArgument('what', InputArgument::REQUIRED, implode('|', array_keys($this->processors)))
            ->addArgument('id', InputArgument::REQUIRED, 'id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $process = $input->getArgument('what');
        if (!in_array($process, array_keys($this->processors))) {
            $this->log("Unknown process $process, available process: " .implode('|', array_keys($this->processors)));
            return;
        }

        $id = $input->getArgument('id');
        return $this->timedActivity("Processing $process on $id", function() use ($id, $process){
            $record = RegistryObjectsRepository::getRecordByID($id);
            if (!$record) {
                $this->log("Record $id not found", "error");
                return;
            }
            $processMethod = new ReflectionMethod($this->processors[$process], 'process');
            $processMethod->invoke(new $this->processors[$process], $record);
            $this->log("Success $process on ($id)", "info");
        });
    }
}