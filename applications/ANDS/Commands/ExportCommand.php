<?php


namespace ANDS\Commands;


use ANDS\RegistryObject;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ExportCommand extends ANDSCommand
{
    private $options = [
        'path' => '/tmp/export/',
        'format' => 'doc-cache-xml',
        'scope' => 'published',
        'batch' => 1000
    ];

    protected $validFormats = ['doc-cache-xml'];
    protected $validScope = ['published', 'drafts'];

    protected function configure()
    {
        $this
            ->setName('export')
            ->setDescription('Export the registry')
            ->setHelp("This command allows you to export the registry in different formats")
            ->addArgument('what', InputArgument::REQUIRED, 'registry')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $what = $input->getArgument("what");

        $stopwatch = new Stopwatch();
        $stopwatch->start('execute');
        if ($what == "registry") {
            $this->exportRegistry();
        }

        if ($what == "logs") {
            $this->exportLogs();
        }
        $event = $stopwatch->stop('execute');

        $this->log("Task completed. duration: {$event->getDuration()}. Memory Usage: {$event->getMemory()}");
    }

    private function exportLogs()
    {
        $dest = $this->options['path'];
        $batch = $this->options['batch'];

        if (!file_exists($dest)) {
            $this->log("Creating directory $dest");
            mkdir($dest, 0775, true);
        }

        if (!is_writable($dest)) {
            $this->log("$dest not writable", "error");
            return;
        }

        // default format
        $scope = $this->options['scope'];

        // TODO default scope is portal_search
        $scope = "portal_search";

        $url = env('ELASTICSEARCH_URL', 'http://localhost:9200');
        $url = rtrim($url, '/');

        $this->log("Exporting $scope events from $url. writing to $dest");

        $this->client = ClientBuilder::create()
            ->setHosts(
                [ $url ]
            )->build();

        $params = [
            'index' => 'portal-*',
            'body' => [
                '_source' => ["doc.@fields.filters", "doc.@fields.user.user_agent", "doc.@timestamp"],
                'size' => 1000,
                "sort" =>  [ "_doc" ],
                'query' => [
                    'match' => [
                        'doc.@fields.event' => [
                            'query' => 'portal_search'
                        ],
                    ]
                ]
            ],
            "scroll" => "1d"
        ];

        $response = $this->client->search($params);
        $docs = $response['hits']['hits'];
        $total = $response['hits']['total'];

        // write the first response
        $content = json_encode($docs);
        $path = $dest . md5($content).".json";
        file_put_contents($path, $content);

        $sofar = count($docs);
        $this->log("($sofar/$total) $path");
        while (array_key_exists("_scroll_id", $response) && count($docs) > 0) {
            $response = $this->client->scroll([
                "scroll" => "1d",
                "scroll_id" => $response["_scroll_id"]
            ]);
            $docs = $response['hits']['hits'];
            $total = $response['hits']['total'];

            // write
            $content = json_encode($docs);
            $path = $dest . md5($content).".json";
            file_put_contents($path, $content);

            $sofar += count($docs);
            $this->log("($sofar/$total) $path");
        }


    }

    private function exportRegistry()
    {
        $dest = $this->options['path'];
        $batch = $this->options['batch'];

        if (!file_exists($dest)) {
            $this->log("Creating directory $dest");
            mkdir($dest, 0775, true);
        }

        if (!is_writable($dest)) {
            $this->log("$dest not writable", "error");
            return;
        }

        // TODO: check format
        $format = $this->options['format'];

        // TODO: check scope
        $scope = $this->options['scope'];

        // published
        $counter = 1;
        RegistryObject::where('status', 'PUBLISHED')
            ->chunk($batch, function ($records) use ($counter, $dest){
                $payload = [];
                foreach ($records as $record) {
                    if ($this->isVerbose()) {
                        $this->log("Exporting ($record->id) $record->title");
                    }

                    if ($currentData = $record->getCurrentData()) {
                        $rifcs = $currentData->data;
                        $payload[$record->id] = $rifcs;
                    } else {
                        $this->log("($record->id) $record->title does not have record data", "error");
                    }
                }
                $path = $dest . md5($records).".xml";

                $content = $this->formatPayload($payload);

                // TODO: Check existed
                file_put_contents($path, $content);

                $this->log("Exported ".count($payload) . "records to $path");
            });

//        $this->log("Exporting ".count($ids)." to $dest in batch of $batch in $format");

    }

    /**
     * TODO: check format
     *
     * @param $payload
     * @return string
     */
    public function formatPayload($payload)
    {

        $xml = "<DOCS>";
        foreach ($payload as $id => $content) {
            $xml .= "<DOC>";
            $xml .= "<DOCNO>$id</DOCNO>";
            $xml .= "<TEXT>$content</TEXT>";
            $xml .= "</DOC>";
        }
        $xml .= "</DOCS>";

        return $xml;
    }

    private function fetchRecordByScope($scope)
    {
        $ids = [];

        if ($scope == 'published') {
            $ids = RegistryObject::where('status' ,'PUBLISHED')->take(20000)->lists('registry_object_id');
        }

        return $ids;
    }
}