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
    private $solrUrl = null;
    private $output = null;

    protected function configure()
    {
        $solrUrl = Config::get('app.solr_url');
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
                    ),
                    new InputOption(
                        'solr_url',
                        's',
                        InputOption::VALUE_OPTIONAL,
                        'SOLR index target URL',
                        $solrUrl
                    )
                ]))
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

            if (isset($concepts['notation'])) {
                $current_notations = (string)$concepts['notation'];
                $current_notation[] = (string)$concepts['notation'];
            } elseif ($type == "iso639-3") {
                $notations = explode("/", $concepts['iri']);
                $current_notations = array_pop($notations);
            } else {
                $current_notations = null;
                $current_notation = null;
            }

            isset($concepts['prefLabel']) ? $current_prefLabel = $concepts['prefLabel'] : $current_prefLabel = $current_notations;

            $concept = [
                'type' => $type,
                'id' => $concepts['iri'],
                'iri' => $concepts['iri'],
                'notation_s' => isset($current_notations) ? (string)$current_notations : NULL,
                'label' => $current_prefLabel,
                'label_s' => $current_prefLabel,
                'search_label_s' => strtolower($current_prefLabel),
                'search_label_ss' => $current_broader,
                'description' => isset($concepts['definition']) ? $concepts['definition'] : '',
                'description_s' => isset($concepts['definition']) ? $concepts['definition'] : '',
                'search_labels_string_s' => implode(" ", $current_broader),
                'broader_labels_ss' => $broader,
                'broader_iris_ss' => $iri,
                'broader_notations_ss' => $notation,
            ];

            $this->output->writeln(
                "Indexing $current_prefLabel",
                OutputInterface::VERBOSITY_VERBOSE
            );

            if ($this->output->isVeryVerbose()) {
                print_r($concept);
            }

            $client = new SolrClient($this->solrUrl);
            $client->setCore('concepts');

            // encode the concept in utf8
            $concept = $this->utf8_encode_recursive($concept);

            // Adding document
            $client->add(
                new SolrDocument($concept)
            );

            if (array_key_exists('narrower', $concepts)) {
                $this->generate_solr(
                    $concepts['narrower'],
                    $current_broader,
                    $current_iri, $current_notation, $type
                );
            }
        }
    }

    function utf8_encode_recursive($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->utf8_encode_recursive($value);
                continue;
            }
            $result[$key] = $value;
            if (is_string($value)) {
                $result[$key] = utf8_encode($value);
            }
        }
        return $result;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $source = $input->getOption('concepts_file');
        $type = $input->getOption('vocab_type');
        $this->solrUrl = $input->getOption('solr_url');

        $output->writeln("Indexing: $type to {$this->solrUrl}");

        // TODO: make dynamic, use the VocabsRegistryAPI to find the latest artefact version
        $conceptsSourceURLs = [
            'anzsrc-for' => 'https://vocabs.ands.org.au/registry/api/resource/versions/28/versionArtefacts/conceptTree',
            'anzsrc-seo' => 'https://vocabs.ands.org.au/registry/api/resource/versions/18/versionArtefacts/conceptTree',

            // gcmd-sci on RVA
            'gcmd' => 'https://vocabs.ands.org.au/registry/api/resource/versions/16/versionArtefacts/conceptTree',

            //'iso639-3' -> should come from file
        ];

        if (in_array($type, array_keys($conceptsSourceURLs))) {
            $source = $conceptsSourceURLs[$type];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $concepts_source = curl_exec($ch);
            curl_close($ch);
        } else {
            $concepts_source = file_get_contents($source);
        }

        $concepts = json_decode($concepts_source, true);

        $this->generate_solr(
            $concepts,
            $broader = [],
            $broader_iri = [],
            $broader_notation = [],
            $type
        );

        $output->writeln('You have indexed concepts of a ' . $type . ' vocabulary from ' . $source . ".");

    }
}