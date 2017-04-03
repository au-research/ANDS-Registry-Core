<?php


namespace ANDS\Task;


use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Simpleue\Job\Job;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class SyncRecordTask implements Job
{
    private $loggingPath = "./logs/ro-sync-reports/";

    public function __construct(OutputInterface $consoleOutput) {
        $this->consoleOutput = $consoleOutput;
    }

    // job should contain the { record_id: :id }
    public function manage($job)
    {
        $data = json_decode($job, true);
        $this->consoleOutput->writeln("Processing: $job");
        $record = RegistryObjectsRepository::getRecordByID($data['record_id']);
        if ($this->syncRecord($record)) {
            $this->consoleOutput->writeln("<info>Processed: $record->id</info>");
            return true;
        }
        return false;
    }

    public function isStopJob($job)
    {
        return false;
    }

    private function syncRecord(RegistryObject $record)
    {
        // TODO: Workaround CI limitation, have to call internal API
        $client = new Client([
            'base_uri' => baseUrl("api/registry/object/"),
            'timeout'  => 360,
        ]);

        try {
            // mark record
            $record->setRegistryObjectAttribute('processing_by', uniqid());

            $url = baseUrl("api/registry/object/$record->id/sync");
            $response = $client->get($url);

            // $this->consoleOutput->writeln($url);

            // unmark record
            $record->deleteRegistryObjectAttribute('processing_by');

            $body = (string) $response->getBody();
            $this->writeLog($record, $body);
            return true;
        } catch (RequestException $e) {
            $this->writeLog($record, $e->getMessage(), "ERROR-");
            return false;
        }
    }

    private function writeLog(RegistryObject $record, $result, $append = "")
    {
        $fs = new Filesystem();
        $fs->dumpFile($this->loggingPath.'/'.$append.$record->id, $result);
    }

}