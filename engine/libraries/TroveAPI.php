<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class:  TroveAPI
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class TroveAPI
{
    private $ci;
    private $apiUrl = 'http://api.trove.nla.gov.au/';
    private $apiKey;

    function __construct()
    {
        $this->ci =& get_instance();
        if ($url = \ANDS\Util\config::get('nla.trove_api_url')) {
            $this->apiUrl = $url;
        }
        $this->apiKey = \ANDS\Util\config::get('nla.trove_api_key');

        if (!$this->apiKey) {
            throw new Exception('Trove API Key required for this functionality');
        }
    }

    function resolveQuery($query, $zone = 'article', $include = 'workversions')
    {
        $queryString = [
            'q' => $query,
            'zone' => $zone,
            'include' => $include,
            'encoding' => 'json',
            'key' => $this->getApiKey()
        ];
        $url = $this->getApiUrl().'result?'.http_build_query($queryString);
        $content = @file_get_contents($url);
        $content = json_decode($content, true);
        if (isset($content['response'])) {
            return $content;
        } else {
            return false;
        }
    }

    /**
     * @return bool|mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param bool|mixed $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return bool|mixed|string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param bool|mixed|string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

}