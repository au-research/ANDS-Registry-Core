<?php
/**
 * Class:  Status API
 * @author: Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */

namespace ANDS\API;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Util\Config;
use GraphAware\Neo4j\Client\ClientBuilder;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\CurlException;
use Illuminate\Database\Capsule\Manager as DB;

class Status_api
{
    public function __construct()
    {
        $this->ci = &get_instance();
        $this->db = $this->ci->load->database('registry', true);
    }

    /**
     * Handling api/status
     * @param array $method
     * @return array
     */
    public function handle($method = array())
    {
        return [
            'database' => $this->getDatabaseStatus(),
            'harvester' => $this->getHarvesterStatus(),
            'taskmanager' => $this->getTaskManagerStatus(),
            'solr' => $this->getSOLRStatus(),
            'mycelium' => $this->getMyceliumStatus(),
            'elasticsearch' => $this->getElasticSearchStatus()
        ];
    }

    /**
     * @return array
     */
    private function getHarvesterStatus()
    {
        $config = Config::get('app.harvester');
        return $this->getHTTPStatus($config['url']);
    }

    /**
     * @return array
     */
    private function getTaskManagerStatus()
    {
        $config = Config::get('app.taskmanager');
        return $this->getHTTPStatus($config['url']);
    }

    /**
     * Returns the all core status for SOLR
     *
     * Hitting admin/cores?action=status
     * @return array
     */
    private function getSOLRStatus()
    {
        $response = $this->getHTTPStatus(Config::get('app.solr_url') .'admin/cores?action=status&wt=json');
        if (array_key_exists('running', $response) && $response['running'] === false) {
            return $response;
        }
        return array_merge(['running' => true], $response['status']);
    }

    /**
     * @return array
     */
    private function getDatabaseStatus()
    {
        initEloquent();
        $config = Config::get('database');
        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = true;
            try {
                $conn = DB::connection($key);

                // get Pdo would throw an exception if the database is not connected correctly
                $conn->getPdo();
                $result[$key] = [
                    'host' => $conn->getConfig('host'),
                    'database' => $conn->getDatabaseName(),
                    'running' => true
                ];
            } catch (\Exception $e) {
                $result[$key] = [
                    'msg' => $e->getMessage(),
                    'running' => false,
                ];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getMyceliumStatus()
    {
        $myceliumURL = Config::get('mycelium.url');
        $client = new MyceliumServiceClient($myceliumURL);

        $info = $client->info();

        return [
            'url' => $myceliumURL,
            'running' => $info->getStatusCode() === 200,
            'status' => json_decode($info->getBody()->getContents(), true)
        ];
    }

    /**
     * @return array
     */
    private function getElasticSearchStatus()
    {
        $url = Config::get('app.elasticsearch_url');
        $response =  $this->getHTTPStatus($url);
        if (array_key_exists('running', $response) && $response['running'] === false) {
            return $response;
        }
        return array_merge(['running' => true], $response);
    }

    /**
     * Helper function to return the possible status of a HTTP endpoint
     *
     * @param $url
     * @return array
     */
    private function getHTTPStatus($url)
    {
        $client = new Client($url);
        try {
            $response = $client->get(null)->send();

            if ($response->getStatusCode() != 200) {
                return [
                    'running' => false,
                    'reason' => "response code {$response->getStatusCode()}",
                    'body' => $response->json()
                ];
            }

            return $response->json();
        } catch (CurlException $e) {
            return [
                'running' => false,
                'reason' => $e->getMessage()
            ];
        }
    }


}