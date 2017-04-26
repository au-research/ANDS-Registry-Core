<?php
/**
 * Created by PhpStorm.
 * User: lwoods
 * Date: 21/02/2017
 * Time: 10:44 AM
 */

namespace ANDS\Commands;

use ANDS\Util\Config;
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
                        'anzsrc-for'
                    )]))
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("This command allows you to add a vocabulary to the Solr Concepts index.");
    }


    private function generate_solr($concepts_array, $broader, $iri, $notation, $type)
    {

        foreach ($concepts_array as $concepts) {

            $current_broader = $broader;
            $current_notation = $notation;
            $current_broader[] = isset($concepts['prefLabel']) ? $concepts['prefLabel']: '' ;
            $current_iri = $iri;
            $current_iri[] = $concepts['iri'];
            if(isset($concepts['notation'])){
                $current_notations =  (string)$concepts['notation'];
                $current_notation[] = (string)$concepts['notation'];
            }elseif ($type=="iso639-3") {
                $notations = explode("/",$concepts['iri']);
                $current_notations = array_pop($notations);
            }else{
                $current_notations = NULL;
                $current_notation = NULL;
            }
            isset($concepts['prefLabel']) ? $current_prefLabel = $concepts['prefLabel'] : $current_prefLabel = $current_notations;

            $concept = array();
            $concept['type'] = $type;
            $concept['id'] = $concepts['iri'];
            $concept['iri'] = $concepts['iri'];
            $concept['notation_s'] = isset($current_notations)? (string)$current_notations : NULL;
            $concept['label'] = $current_prefLabel;
            $concept['label_s'] = $current_prefLabel;
            $concept['search_label_s'] = strtolower($current_prefLabel);
            $concept['search_label_ss'] = $current_broader;
            $concept['description'] = isset($concepts['definition']) ? $concepts['definition'] : '';
            $concept['description_s'] = isset($concepts['definition']) ? $concepts['definition'] : '';
            $concept['search_labels_string_s'] = implode(" ", $current_broader);
            $concept['broader_labels_ss'] = $broader;
            $concept['broader_iris_ss'] = $iri;
            $concept['broader_notations_ss'] = $notation;

            $client = new SolrClient(Config::get('app.solr_url'));

            $client->setCore('concepts');

            // Adding document
            $result = $client->add(
                new SolrDocument($concept)
            );

            if (isset($concepts['narrower'])) {

                $this->generate_solr($concepts['narrower'], $current_broader, $current_iri,$current_notation,$type);
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
        $broader_iri = array();
        $broader_notation = array();

        $this->generate_solr($concepts_array, $broader,$broader_iri, $broader_notation, $type);

        $output->writeln('You have indexed concepts of a ' . $type . ' vocabulary from ' . $source . ".");

    }

}