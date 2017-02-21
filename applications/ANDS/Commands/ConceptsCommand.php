<?php
/**
 * Created by PhpStorm.
 * User: lwoods
 * Date: 21/02/2017
 * Time: 10:44 AM
 */

namespace ANDS\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class ConceptsCommand extends Command
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "ands")
            ->setName('concepts:index')

            // the short description shown while running "php ands concepts"
            ->setDescription('Add a vocabulary to the Solr Concepts index.')

            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'concepts_file',
                        'f',
                        InputOption::VALUE_REQUIRED,
                        'concepts_json_tree',
                        'concepts_tree.json'
                    )]))

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command allows you to add a vocabulary to the Solr Concepts index.")
        ;
    }


    private function generate_solr($concepts_array, $broader){

        foreach($concepts_array as $concepts){

            $broader .= ", ".$concepts['prefLabel'];

            print_r("id : " .$concepts['iri']."\n");
            print_r("label : " .$concepts['prefLabel']."\n");
            print_r(isset($concepts['definition']) ? "description : " .$concepts['definition']."\n" : "description : " ."\n");
            print_r("broarder : ".$broader."\n");

            //$broader_list[] = $broader;

            isset($concepts['narrower']) ? $this->generate_solr($concepts['narrower'], $broader) : '';



        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $source = $input->getOption('concepts_file');

        $concepts_source = file_get_contents($source);

        $concepts_array = json_decode($concepts_source, true);

        // $broader = array();

        $this->generate_solr($concepts_array[0]['narrower'],'' );

        $output->writeln('You are about to ');

        $output->writeln('index a concept.');

        $output->writeln($source);
    }

}