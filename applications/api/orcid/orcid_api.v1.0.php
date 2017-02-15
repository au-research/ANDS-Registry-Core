<?php
namespace ANDS\API;

use \Exception as Exception;

class Orcid_api
{

    private $client = null;

    public function handle($method = array())
    {
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
        $search_endpoint = 'https://pub.orcid.org/v1.1/search/orcid-bio?q='.urlencode($query);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $search_endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        $data= simplexml_load_string($result);
        return $data;
    }

    private function lookupOrcid($identifier)
    {

        $lookup_endpoint = 'https://pub.orcid.org/v1.1/'.$identifier;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $lookup_endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $result = curl_exec($ch);
        curl_close($ch);
        $data= simplexml_load_string($result);
        return $data;

    }


    public function __construct()
    {
        $this->ci = &get_instance();
        require_once BASE . 'vendor/autoload.php';
    }
}
