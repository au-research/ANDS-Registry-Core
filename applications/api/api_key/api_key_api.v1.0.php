<?php
/**
 * Class:  API Key
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @author: Liz Woods <liz.woods@ands.org.au>
 */

namespace ANDS\API;

use \Exception as Exception;


class Api_key_api
{
    private $ci;
    private $db;
    private $params;

    /**
     * Task_api constructor.
     */
    public function __construct()
    {
        $this->ci = &get_instance();
        $this->db = $this->ci->load->database('registry', true);
        require_once APP_PATH . 'vendor/autoload.php';
    }

    public function handle($method = array())
    {
        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : false,
            'identifier' => isset($method[2]) ? $method[2] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
        );

        switch ($this->params['submodule']) {
            case 'register':
                return $this->registerAPIKeys();
            default:
                if ($this->params['submodule']) {
                    return $this->getAPIKey($this->params['submodule']);
                } else {
                    return $this->listAPIKeys();
                }
        }
    }

    private function registerAPIKeys()
    {
        return $this->ci->input->post();
    }

    private function getAPIKey($apiKey)
    {
        $query = $this->db->get_where('api_keys', ['api_key' => $apiKey]);
        if (!$query || $query->num_rows() == 0) throw new Exception("No API Keys found");
        return $query->result_array();
    }

    public function listAPIKeys()
    {
        $query = $this->db->get('api_keys');
        if (!$query || $query->num_rows() == 0) throw new Exception("No API Keys found");
        return $query->result_array();
    }
}