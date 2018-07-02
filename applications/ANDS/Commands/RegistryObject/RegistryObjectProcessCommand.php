<?php


namespace ANDS\Commands\RegistryObject;


use ANDS\Commands\ANDSCommand;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Registry\Providers\LinkProvider;
use ANDS\Registry\Providers\QualityMetadataProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\SubjectProvider;
use ANDS\Registry\Providers\ScholixProvider;
use ANDS\Registry\Providers\TitleProvider;
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
        'relationship' => RelationshipProvider::class,
        'title' => TitleProvider::class,
        'core' => CoreMetadataProvider::class,
        'date' => DatesProvider::class,
        'graph' => GraphRelationshipProvider::class
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

        $ids = explode(',', $input->getArgument('id'));
        foreach ($ids as $id) {
            $this->log("Processing $process on $id");
            $record = RegistryObjectsRepository::getRecordByID($id);
            if (!$record) {
                $this->log("Record $id not found", "error");
                continue;
            }
            $processMethod = new ReflectionMethod($this->processors[$process], 'process');
            $processMethod->invoke(new $this->processors[$process], $record);
            $this->log("Success $process on ($id)", "info");
        }
    }
}