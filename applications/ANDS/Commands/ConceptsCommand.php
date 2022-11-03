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

    private function generate_concept_solr($concepts){
        foreach($concepts as $concept){
            $this->output->writeln(
                "Indexing ".$concept['label_s'],
                OutputInterface::VERBOSITY_VERBOSE
            );

            if ($this->output->isVeryVerbose()) {
                print_r($concept);
            }

            $client = new SolrClient($this->solrUrl);

            $client->setCore('concepts');

            //   Adding document
           $result =  $client->add(
                new SolrDocument($concept)
            );

        }
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

          //   encode the concept in utf8
            $concept = $this->utf8_encode_recursive($concept);


          //   Adding document
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

    protected function getConceptsFromVocab($type, $conceptsVocabUrl){
        $_page = 0;
        //need to create the concepts
        $concepts_in_vocab = true;
        $concepts = [];
        $count = 0;
        header("Content-Type: text/html; charset=utf-8");
        while($concepts_in_vocab){
            $conceptsUrl = $conceptsVocabUrl.$_page;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $conceptsUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $concepts_source = curl_exec($ch);
            $file_concepts = json_decode($concepts_source, true);

            if(isset($file_concepts['result']['items'][0])) {
                $concepts_array = $file_concepts["result"]["items"];
                foreach ($concepts_array as $concept_array) {
                    $broader_iris_ss = NULL;
                    //Set the flag if we need to extract the broader concept info later
                    if (isset($concept_array['broader'])) {
                        $broader_iris_ss[] = $concept_array['broader'];
                    }
                    if( isset ($concept_array['prefLabel']['_value'])){
                        $concept_array['prefLabel']['_value'] = str_replace("’","'",$concept_array['prefLabel']['_value']);
                    }
                    $concept = [
                        'broader_iris_ss' => $broader_iris_ss,
                        'broader_labels_ss' => NULL,
                        'broader_notations_ss' => NULL,
                        'id' => $concept_array['_about'],
                        'iri' => [$concept_array['_about']],
                        'label' => isset($concept_array['prefLabel']['_value']) ? [(string)$concept_array['prefLabel']['_value']] : NULL,
                        'label_s' => isset($concept_array['prefLabel']['_value']) ? (string)$concept_array['prefLabel']['_value'] : NULL,
                        'notation_s' => isset($concept_array['notation']) ? (string)$concept_array['notation'] : NULL,
                        'search_label_s' => isset($concept_array['prefLabel']['_value']) ? strtolower((string)$concept_array['prefLabel']['_value']) : NULL,
                        'search_label_ss' => isset($concept_array['prefLabel']['_value']) ? [(string)$concept_array['prefLabel']['_value']] : NULL,
                        'description' => isset($concept_array['definition']) ? $concept_array['definition'] : '',
                        'description_s' => isset($concept_array['definition']) ? $concept_array['definition'] : '',
                        'search_labels_string_s' => isset($concept_array['prefLabel']['_value']) ? (string)$concept_array['prefLabel']['_value'] : NULL,
                        'type' => [$type]
                    ];
                    $concepts[$concept['notation_s']] = $concept;
                    $count++;
                }
            }else{
                $concepts_in_vocab = false;
            }
            curl_close($ch);
            $_page = $_page + 1;
        }

        foreach($concepts as $concept){
            //if this concept has broader concepts let's get their info to add to the index
            if($type=='anzsrc-seo-2020') $t = 'seo';
            if($type=='anzsrc-for-2020') $t = 'for';
            if(isset($concept['broader_iris_ss'])){
                $broader_notation = str_replace('https://linked.data.gov.au/def/anzsrc-'.$t.'/2020/','',$concept['broader_iris_ss'][0]);
                $concepts[$concept['notation_s']]['search_labels_string_s'] = $concepts[$broader_notation]['search_labels_string_s'] . " " .$concept['search_labels_string_s'];
                $concepts[$concept['notation_s']]["broader_labels_ss"][] = $concepts[$broader_notation]['label_s'];
                $concepts[$concept['notation_s']]["broader_notations_ss"][] = $concepts[$broader_notation]['notation_s'];
                $concepts[$concept['notation_s']]['search_label_ss'][] = $concepts[$broader_notation]['label_s'];
                if(isset($concepts[$broader_notation]['broader_iris_ss'])) {
                    $broader_notation_ = str_replace('https://linked.data.gov.au/def/anzsrc-'.$t.'/2020/', '', $concepts[$broader_notation]['broader_iris_ss'][0]);
                    $concepts[$concept['notation_s']]['broader_iris_ss'][] = $concepts[$broader_notation]['broader_iris_ss'][0];
                    $concepts[$concept['notation_s']]["broader_labels_ss"][] = $concepts[$broader_notation_]['label_s'];
                    $concepts[$concept['notation_s']]["broader_notations_ss"][] = $concepts[$broader_notation_]['notation_s'];
                    $concepts[$concept['notation_s']]['search_labels_string_s'] = $concepts[$broader_notation_]['search_labels_string_s'] .
                        " " . $concepts[$broader_notation]['search_labels_string_s'] . " " . $concept['search_labels_string_s'];
                    $concepts[$concept['notation_s']]['search_label_ss'][] = $concepts[$broader_notation_]['label_s'];
                }
            }
        }
        return $concepts;
    }

    protected function generate_source_solr($type,$concepts_source,$output){
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $source = $input->getOption('concepts_file');
        $type = $input->getOption('vocab_type');
        $this->solrUrl = $input->getOption('solr_url');
        $output->writeln("Indexing: $type to {$this->solrUrl} for {$type}");
        $conceptsSourceURLs = [
            'anzsrc-for' => 'https://vocabs.ardc.edu.au/registry/api/resource/versions/28/versionArtefacts/conceptTree',
            'anzsrc-seo' => 'https://vocabs.ardc.edu.au/registry/api/resource/versions/18/versionArtefacts/conceptTree',

            // gcmd-sci on RVA
            'gcmd' => 'https://vocabs.ardc.edu.au/registry/api/resource/versions/16/versionArtefacts/conceptTree',

            //'iso639-3' -> should come from file
        ];

        // to make dynamic, use the VocabsRegistryAPI to find the latest artefact version
         $conceptsVocabURLs = [
             'anzsrc-for-2020' => "https://vocabs.ardc.edu.au/repository/api/lda/anzsrc-2020-for/concept.json?_page=",
             'anzsrc-seo-2020' => "https://vocabs.ardc.edu.au/repository/api/lda/anzsrc-2020-seo/concept.json?_page="
        ];

        if (in_array($type, array_keys($conceptsVocabURLs))){
            $concepts = $this->getConceptsFromVocab($type,$conceptsVocabURLs[$type]);
            $this->generate_concept_solr($concepts);
            $output->writeln('You have indexed concepts of a ' . $type . ' vocabulary from ' . $conceptsVocabURLs[$type] . ".");
        } elseif (in_array($type, array_keys($conceptsSourceURLs))) {
            $source = $conceptsSourceURLs[$type];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $source);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $concepts_source = curl_exec($ch);
            curl_close($ch);
            $this->generate_source_solr($type,$concepts_source,$output);
        }
        else {
            $concepts_source = file_get_contents($source);
            $this->generate_source_solr($type,$concepts_source,$output);
        }
    }
}