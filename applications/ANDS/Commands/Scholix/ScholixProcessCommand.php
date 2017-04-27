<?php


namespace ANDS\Commands\Scholix;


use ANDS\Registry\Providers\ScholixProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScholixProcessCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('scholix')
            ->setDescription('Identify Scholixable records')
            ->setHelp("This command allows you to identify and process scholixable records")

            ->addArgument('what', InputArgument::REQUIRED, 'identify|process')
            ->addArgument('id', InputArgument::OPTIONAL, 'id of the record')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_REQUIRED,
                'Force process on all',
                false
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        initEloquent();

        $command = $input->getArgument("what");

        if ($command == "identify") {
            $this->identify($input, $output);
        }

        if ($command == "process") {
            $this->process($input, $output);
        }
    }

    private function identify(InputInterface $input, OutputInterface $output)
    {
        $scholixableRecords = RegistryObject::where('class', 'collection')
            ->whereIn('type', ['dataset', 'collection'])
            ->where('status', 'PUBLISHED')
            ->whereHas('registryObjectAttributes', function($query){
                return $query
                    ->where('attribute', 'scholixable')
                    ->where('value', true);
            });
        $count = $scholixableRecords->count();

        $nonScholixableRecords = RegistryObject::where('class', 'collection')
            ->whereIn('type', ['dataset', 'collection'])
            ->where('status', 'PUBLISHED')
            ->whereHas('registryObjectAttributes', function($query){
                return $query
                    ->where('attribute', 'scholixable')
                    ->where('value', 0);
            });
        $countNon = $nonScholixableRecords->count();

        $unchecked = RegistryObject::where('class', 'collection')
            ->whereIn('type', ['dataset', 'collection'])
            ->where('status', 'PUBLISHED')
            ->whereDoesntHave('registryObjectAttributes', function($query){
                return $query
                    ->where('attribute', 'scholixable');
            });
        $countUnchecked = $unchecked->count();

        $output->writeln("There are $count scholixable Records");
        $output->writeln("There are $countNon nonScholixableRecords");
        $output->writeln("There are $countUnchecked un-checked records");
    }

    private function process(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument("id");

        if (!$id) {
            $unchecked = RegistryObject::where('class', 'collection')
                ->whereIn('type', ['dataset', 'collection'])
                ->where('status', 'PUBLISHED');

            if ($input->getOption('force') === false) {
                $unchecked = $unchecked
                    ->whereDoesntHave('registryObjectAttributes', function($query){
                    return $query
                        ->where('attribute', 'scholixable');
                });
            }

            $progressBar = new ProgressBar($output, $unchecked->count());
            foreach ($unchecked->get() as $record) {
                $progressBar->advance(1);
                ScholixProvider::process($record);
            }
            $progressBar->finish();
            return;
        }

        $record = RegistryObjectsRepository::getRecordByID($id);
        $output->writeln("Processing $record->title ($record->id) for scholixable");

        $scholixable = ScholixProvider::isScholixable($record);
        if ($scholixable) {
            ScholixProvider::process($record);
            $output->writeln("This record is scholixable. Saved!");
        } else {
            $output->writeln("This record NOT is scholixable");
        }
    }

}