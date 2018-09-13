<?php
namespace ANDS\API;

use ANDS\Cache\Cache;
use ANDS\Util\ORCIDAPI;
use \Exception as Exception;

class Orcid_api
{

    private $client = null;

    public function handle($method = array())
    {
        header("Access-Control-Allow-Origin: *");
        $this->ci = &get_instance();

        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : 'search',
            'identifier' => isset($method[2]) ? $method[2] : false,
        );
        try {
            if ($this->params['submodule'] == 'search') {
                return $this->searchOrcid();
            } elseif ($this->params['submodule'] == 'lookup') {
                return $this->lookupOrcid($this->params['identifier']);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }

    private function searchOrcid()
    {
        $query = urldecode($this->ci->input->get('q'));

        return Cache::file()->rememberForever("orcid_api_search.$query", function () use ($query) {
            $publicClient = ORCIDAPI::getPublicClient();
            $data = $publicClient->get('search/?q=' . urlencode($query) . '&rows=10');
            $content = json_decode($data->getBody()->getContents(), true);
            $result = array();
            foreach ($content['result'] as $orcid) {
                $publicClient = ORCIDAPI::getPublicClient();
                $orcidID = $orcid['orcid-identifier']['path'];
                $extracted = Cache::file()->rememberForever("orcid.$orcidID", function () use ($publicClient, $orcidID) {
                    $orcid_info = $publicClient->get($orcidID);
                    $result = json_decode($orcid_info->getBody()->getContents(), true);
                    return $result;
                });
                $result['orcid-search-results'][] = array("person" => $extracted['person'], "orcid" => $orcid['orcid-identifier']['path']);
            }
            return $result;
        });
    }

    private function lookupOrcid($identifier)
    {
        $publicClient = ORCIDAPI::getPublicClient();
        $orcid_info = $publicClient->get($identifier);
        $extracted = json_decode($orcid_info->getBody()->getContents(), true);
        $return = array("person"=>$extracted['person'],"orcid"=>$identifier);
        return $return;
    }
    
    public function __construct()
    {
        $this->ci = &get_instance();
        require_once BASE . 'vendor/autoload.php';
    }
}
