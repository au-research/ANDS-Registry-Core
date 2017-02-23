<?php
/**
 * Created by PhpStorm.
 * User: lwoods
 * Date: 21/02/2017
 * Time: 10:44 AM
 */

namespace ANDS\Commands;

use MinhD\SolrClient\SolrClient;
use MinhD\SolrClient\SolrDocument;
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
                    ),
                    new InputOption(
                        'vocab_type',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Vocab type',
                        'ANZSRC-for'
                    )]))
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command allows you to add a vocabulary to the Solr Concepts index.");
    }


    private function generate_solr($concepts_array, $broader, $type)
    {

        foreach ($concepts_array as $concepts) {

            $current_broader = $broader;
            $current_broader[] = $concepts['prefLabel'];

            $concept = array();
            $concept['type'] = $type;
            $concept['id'] = $concepts['iri'];
            $concept['iri'] = $concepts['iri'];
            $concept['notation'] = isset($concepts['notation'])? (string)$concepts['notation'] : '';
            $concept['label'] = $concepts['prefLabel'];
            $concept['label_s'] = $concepts['prefLabel'];
            $concept['search_label_ss'] = $current_broader;
            $concept['description'] = isset($concepts['definition']) ? $concepts['definition'] : '';
            $concept['description_s'] = isset($concepts['definition']) ? $concepts['definition'] : '';
            $concept['search_labels_string_s'] = implode(" ", $current_broader);
            $concept['broader_labels_ss'] = $broader;

           // print_r($concept);

            $client = new SolrClient('devl.ands.org.au', '8983');
            $client->setCore('concepts');

            // Adding document
            $client->add(
                new SolrDocument($concept)
            );
            $client->commit();

            if (isset($concepts['narrower'])) {
                $this->generate_solr($concepts['narrower'], $current_broader, $type);
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $source = $input->getOption('concepts_file');

        $type = $input->getOption('vocab_type');

        $concepts_source = file_get_contents($source);

        $concepts_array = json_decode($concepts_source, true);

        $broader = array();

        $this->generate_solr($concepts_array[0]['narrower'], $broader, $type);

        $output->writeln('You have indexed concepts of a ' . $type . ' vocabulary from ' . $source . ".");

    }

}