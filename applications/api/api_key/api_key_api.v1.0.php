<?php
/**
 * Class:  API Key
 * @author: Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @author: Liz Woods <liz.woods@ardc.edu.au>
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
        require_once BASE . 'vendor/autoload.php';
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
       // return $this->ci->input->post();
        if (!$this->ci->input->post('organisation') || !$this->ci->input->post('contact_email'))
        {
            throw new Exception("One of the mandatory fields (Organisation name or Contact Email) were not entered. Please try again.");
        }
        else
        {
            // Generate a random API key hash
            $api_key = substr(md5(mt_rand()), 0, 12);

            $query = $this->db->get_where('api_keys', array('api_key'=>$api_key));
            if ($query->num_rows == 0)
            {
                $this->db->insert('api_keys',
                    array(	'api_key' => $api_key,
                        'owner_email'=>$this->ci->input->post('contact_email'),
                        'owner_organisation'=>$this->ci->input->post('organisation'),
                        'owner_purpose'=>$this->ci->input->post('purpose'),
                        'owner_sector'=>$this->ci->input->post('sector'),
                        'owner_ip'=>$this->ci->input->post('ip'),
                        'created'=>time()
                    ));
            }
            else
            {
                throw new Exception("API Key could not be generated (numeric error = ".$api_key."). Please try again.");
            }

            $data["api_key"] = $api_key;
            $data["organisation"] = $this->ci->input->post('organisation');

            return $data;

        }
    }

    private function getAPIKey($apiKey)
    {
        $query = $this->db->get_where('api_keys', ['api_key' => $apiKey]);
        if (!$query || $query->num_rows() == 0) throw new Exception("API Key ".$apiKey." not found");
        return $query->result_array();
    }

    public function listAPIKeys()
    {
        $query = $this->db->get('api_keys');
        if (!$query || $query->num_rows() == 0) throw new Exception("No API Keys found");
        return $query->result_array();
    }
}