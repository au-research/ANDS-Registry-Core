<?php
namespace ANDS\API\Registry\Handler;
use ANDS\Log\Log;
use ANDS\Mycelium\Paginator;
use ANDS\Util\Config;
use Apache_Solr_Service;
use Exception;
class SolrQueryHandler extends Handler
{

    /**
     * Handles registry/solrquery
     * serves as a proxy-pass for local solr search service
     */

    function handle()
    {
        header("Access-Control-Allow-Origin: *");
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/json";
        // might want to add an API key verification here
        // Based on https://gist.github.com/jpmckinney/2123215
        if (isset($_POST['query'])) {
            $params = array();
            $params['start'] = 0;
            $params['rows'] = 10;
            $keys = '';
            $core = 'portal';
            $apiKey = "";
            // The names of Solr parameters that may be specified multiple times.
            $multivalue_keys = array('bf', 'bq', 'facet.date', 'facet.date.other', 'facet.field', 'facet.query', 'fq', 'pf', 'qf');
            $pairs = explode('&', $_POST['query']);
            foreach ($pairs as $pair) {
                list($key, $value) = explode('=', $pair, 2);
                $value = urldecode($value);
                if (in_array($key, $multivalue_keys)) {
                    $params[$key][] = $value;
                } elseif ($key == 'q') {
                    $keys = $value;
                } elseif ($key == 'core') {
                    $core = "$value/";
                } elseif ($key == 'api_key') {
                    $apiKey = $value;
                }
                else{
                    $params[$key] = $value;
                }
            }
            $solrUrl = Config::get('app.solr_url');
            $parsed_url = parse_url($solrUrl);
            $solr = new Apache_Solr_Service($parsed_url['host'], $parsed_url["port"], $parsed_url['path']. $core);
            try {
                $response = $solr->search($keys, $params['start'], $params['rows'], $params, 'GET');
            } catch (Exception $e) {
                print_r($e);
            }

            print $response->getRawResponse();
        }
    }


}

