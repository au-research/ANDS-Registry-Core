<?php
namespace ANDS\API;

use \Exception as Exception;

class Doi_api
{

    private $client = null;

    public function handle($method = array())
    {
        $this->ci = &get_instance();
        $this->dois_db = $this->ci->load->database('dois', true);



        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : 'list',
            'identifier' => isset($method[2]) ? $method[2] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
        );

        //everything under here requires a client, app_id
        $this->getClient();

        //get a potential DOI
        if ($this->params['object_module']) {
            array_shift($method);
            $potential_doi = join('/',$method);
            if ($doi = $this->getDOI($potential_doi)) {
                $doi->title = $this->getDoiTitle($doi->datacite_xml);
                return $doi;
            }
        }

        try {
            if ($this->params['submodule'] == 'list') {
                return $this->listDois();
            } elseif ($this->params['submodule'] == 'log') {
                return $this->activitiesLog();
            } elseif ($this->params['submodule'] == 'client') {
                return $this->clientDetail();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }

    private function getAssociateAppID($role_id)
    {
        if (!$role_id) throw new Exception('role id required');
        $result = array();
        $roles_db = $this->ci->load->database('roles', true);
        $user_affiliations = array('1');
        $roles_db->distinct()->select('*')
                // ->where_in('child_role_id', $user_affiliations)
                ->where('role_type_id', 'ROLE_DOI_APPID      ', 'after')
                ->join('roles', 'role_id = parent_role_id')
                ->from('role_relations');
        $query = $roles_db->get();

        dd($query->result());

        if ($query->num_rows() > 0) {
            foreach ($query->result() AS $r) {
                $result[] = $r->parent_role_id;
            }
        }
        return $result;
    }

    private function getDOI($doi)
    {
        $query = $this->dois_db
            ->where('doi_id', $doi)
            ->get('doi_objects');
        if ($query->num_rows() > 0) {
            $result = $query->first_row();
            return $result;
        } else return false;
    }

    private function getClient()
    {
        $app_id = $this->ci->input->get('app_id') ? $this->ci->input->get('app_id') : false;

        if (!$app_id) {
            throw new Exception('App ID required');
        }

        $query = $this->dois_db
            ->where('app_id', $app_id)
            ->select('*')
            ->get('doi_client');

        if (!$this->client = $query->result()) {
            throw new Exception('Invalid App ID');
        }

        //permitted_url_domains
        $this->client = array_pop($this->client);
        $query = $this->dois_db
            ->where('client_id',$this->client->client_id)
            ->select('client_domain')
            ->get('doi_client_domains');
        foreach ($query->result_array() AS $domain) {
            $this->client->permitted_url_domains[] =  $domain['client_domain'];
        }
    }

    private function clientDetail()
    {
        return array(
            'client' => $this->client,
        );
    }

    private function listDois()
    {
        $query = $this->dois_db
            ->order_by('updated_when', 'desc')
            ->order_by('created_when', 'desc')
            ->where('client_id', $this->client->client_id)
            ->where('status !=', 'REQUESTED')
            ->select('*')
            ->get('doi_objects');

        $data['dois'] = array();
        foreach ($query->result() as $doi) {
            $obj = $doi;
            $obj->title = $this->getDoiTitle($doi->datacite_xml);
            $data['dois'][] = $obj;
        }
        return $data;
    }

    private function getDoiTitle($doiXml)
    {
        $doiObjects = new \DOMDocument();
        $titleFragment = 'No Title';
        if (strpos($doiXml, '<') === 0) {
            $result = $doiObjects->loadXML(trim($doiXml));
            $titles = $doiObjects->getElementsByTagName('title');

            if ($titles->length > 0) {
                $titleFragment = '';
                for ($j = 0; $j < $titles->length; $j++) {
                    if ($titles->item($j)->getAttribute("titleType")) {
                        $titleType = $titles->item($j)->getAttribute("titleType");
                        $title = $titles->item($j)->nodeValue;
                        $titleFragment .= $title . " (" . $titleType . ") ";
                    } else {
                        $titleFragment .= $titles->item($j)->nodeValue;
                    }
                }
            }
        } else {
            $titleFragment = $doiXml;
        }

        return $titleFragment;

    }

    private function activitiesLog()
    {
        $offset = $this->ci->input->get('start') ? $this->ci->input->get('start') : 0;
        $limit = $this->ci->input->get('limit') ? $this->ci->input->get('limit') : 50;
        $query = $this->dois_db
            ->order_by('timestamp', 'desc')
            ->where('client_id', $this->client->client_id)
            ->select('*')
            ->limit($limit)->offset($offset)
            ->get('activity_log');
        $data['activities'] = $query->result();
        return $data;
    }

    public function __construct()
    {
        $this->ci = &get_instance();
        require_once APP_PATH . 'vendor/autoload.php';
    }
}
