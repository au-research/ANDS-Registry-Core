<?php

namespace ANDS\Commands\Script;

use ANDS\DataSource;
use ANDS\Payload;
use ANDS\Repository\DataSourceRepository;
use ANDS\Util\Config;
use ANDS\Util\XMLUtil;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NLAPullBack extends GenericScript implements GenericScriptRunnable
{

    protected $identifierLimit = 2000;
    private $config = null;

    private $nlaIdentifiers = [];
    private $dataSource = null;

    /**
     * Generate party records for any and all registry objec that has an NLA
     * Identifier.
     * configuration is available at config/nla.php
     * Records are created in a specific data source
     */
    public function run()
    {
        $this->config = Config::get('nla');
        $this->detectDataSource();
        $this->collectNLAIdentifiers();

        $count = count($this->nlaIdentifiers);
        $this->info("Processing {$count} nla identifiers");

        $payload = "";
        $this->progressStart(count($this->nlaIdentifiers));
        foreach ($this->nlaIdentifiers as $identifier) {
            $this->progressAdvance(1);
            try {
                $rifcs = $this->getRIFCSFromNLAIdentifier($identifier);
                $rifcs = XMLUtil::unwrapRegistryObject($rifcs);
                $this->debug("Found rifcs for $identifier");
                $payload .= $rifcs;
            } catch (Exception $e) {
                $this->error("Failed processing $identifier. {$e->getMessage()}");
            }
        }
        $payload = XMLUtil::wrapRegistryObject($payload);
        $this->progressFinish();

        $this->log("Starting importing process...");
        $this->import($payload);
        $this->log("Finished...");
    }

    /**
     * Currently invoke the import pipeline using import via XML
     *
     * TODO: Need to fix the import pipeline to not rely on CodeIgniter
     * @param $payload
     */
    private function import($payload)
    {
        // make payload
        $xml = trim($payload);
        $batchID = "SCHEDULED-XML-".md5($xml).'-'.time();
        $file = Payload::write($this->dataSource->data_source_id, $batchID, $xml);

        $this->log("Payload writen at: {$file}");

        try {
            $client = new Client(['base_uri' => baseUrl()]);
            $response = $client->request(
                'POST',
                '/api/registry/import',
                [
                    'form_params' => [
                        'ds_id' => $this->dataSource->data_source_id,
                        'batch_id' => $batchID,
                        'source' => 'NLAPullBack',
                        'name' => 'NLA Pull Back Job'
                    ]
                ]
            );
            $result = $response->getBody()->getContents();

            $json = json_decode($result, true);
            $taskID = $json['data']['id'];
            $this->info("TaskID: {$taskID}");

            $path = "/tmp/".uniqid();
            $this->log("Response writen to $path");
            file_put_contents($path, $result);

        } catch (RequestException $e) {
            $this->error($e->getResponse()->getBody());
        }

    }

    /**
     * Detect the data source
     * Populate $this->dataSource
     */
    private function detectDataSource()
    {
        $key = $this->config['datasource']['key'];
        $this->log("Detecting if there's a data source with key {$key}");
        $this->dataSource = DataSource::where('key', $key)->get()->first();
        if (!$this->dataSource) {
            $this->info("No data source found. Proceeding to automatically create a data source");
            $this->createDataSource();
        }

        $this->info("Importing to Data source {$this->dataSource->title}({$this->dataSource->data_source_id})...");
    }

    /**
     * Create the NLA data source automatically if none is found
     * called by detection
     */
    private function createDataSource()
    {
        $this->debug("Creating data source");
        $this->dataSource = DataSourceRepository::createDataSource(
            $this->config['datasource']['key'],
            $this->config['datasource']['title'],
            'SYSTEM'
        );
        $this->dataSource->setDataSourceAttribute("manual_publish", 0);
        $this->dataSource->setDataSourceAttribute("qa_flag", 0);

        $this->log("Data source {$this->dataSource->title}({$this->dataSource->data_source_id}) created");
    }

    /**
     * Populate the NLAIdentifiers to be fetched
     */
    private function collectNLAIdentifiers()
    {
        // find all records that has an nla identifier
        $this->log("Fetching all records which are related to NLA identifiers or have an NLA party identifier...");

        $prefix = $this->config['party']['prefix'];

        $this->nlaIdentifiers = [];

        // TODO get them out of Neo4J
        // "MATCH (n:Identifier{identifierType:{$prefix}}}) RETURN n.identifier LIMIT 25"
    }

    public function getRIFCSFromNLAIdentifier($identifier)
    {
        $this->debug("Processing $identifier");

        $url = $this->config['api']['url'] . "?query=rec.identifier=%22" . $identifier . "%22&version=1.1&operation=searchRetrieve&recordSchema=http%3A%2F%2Fands.org.au%2Fstandards%2Frif-cs%2FregistryObjects";
        $this->debug($url);
        $response = curl_file_get_contents($url);

        if (!$response) {
            throw new Exception("No response from server. URL: {$url}");
        }

        // parse
        $sxml = @simplexml_load_string($response);
        if (!$sxml) {
            throw new Exception("No valid data! " . $url);
        }

        // get count
        $sxml->registerXPathNamespace("srw",
            "http://www.loc.gov/zing/srw/");
        $count = $sxml->xpath("//srw:searchRetrieveResponse/srw:numberOfRecords");
        if (is_array($count)) {
            // todo: check count
            $count = array_pop($count);
            $count = (int)$count;
            if ($count <= 0) {
                throw new Exception("Identifier $identifier not found");
            }
        }

        $data = $sxml->xpath("//srw:recordData");
        if (!is_array($data)) {
            throw new Exception("Failed obtaining srw:recordData");
        }

        // Get the matching element
        $data = array_pop($data);
        if (is_object($data) && $data->registryObjects && !empty($data->registryObjects)) {
            $xml = $data->registryObjects->asXML();
            return $xml;
        } else {
            throw new Exception("No registryObjects elements discovered inside SRW response: $identifier URL: $url");
        }

    }
}