<?php


namespace ANDS\Commands;


use ANDS\RegistryObject;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

        if ($what == "registry") {
            $this->exportRegistry();
            return;
        }

        if ($what == "logs") {
            $this->exportLogs();
            return;
        }
    }

    private function exportLogs()
    {
        return;
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