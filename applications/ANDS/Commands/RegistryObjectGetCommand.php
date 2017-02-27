<?php


namespace ANDS\Commands;


use ANDS\API\Task\ImportSubTask\IndexRelationship;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RegistryObjectGetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('ro:get')
            ->setDescription('Get something from ro')
            ->setHelp("This command allows you to interrogate a registry object")

            ->addArgument('any', InputArgument::REQUIRED, 'id or key')
            ->addArgument('what', InputArgument::REQUIRED, 'components')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        initEloquent();

        $any = $input->getArgument("any");

        $record = RegistryObjectsRepository::getRecordByID($any);
        if (!$record) {
            $record = RegistryObjectsRepository::getPublishedByKey($any);
        }

        if (!$record) {
            throw new Exception("Can't find record $any");
        }

        $what = $input->getArgument("what");

        switch ($what) {
            case "attr":
            case "attributes":
            case "attribute":
                var_dump($record->attributesToArray());
                break;
            case "relationships":
                var_dump(RelationshipProvider::getMergedRelationships($record));
                break;
            case "relationships-count":
                $count = count(RelationshipProvider::getMergedRelationships($record));
                $reverseCount = count(RelationshipProvider::getReverseRelationship($record));
                $explicitCount = count(RelationshipProvider::getDirectRelationship($record));
                $output->writeln("Merged: $count");
                $output->writeln("Explicit: $explicitCount");
                $output->writeln("Reverse Explicit: $reverseCount");
                break;
            case "relations-index-generated":
                $indexTask = new IndexRelationship();
                $relationships = RelationshipProvider::getMergedRelationships($record);
                $index = $indexTask->getRelationshipIndex($relationships);
                var_dump($index);
                $output->writeln(count($index));
                break;
            default:
                $output->writeln("Unknown $what");
        }
    }
}