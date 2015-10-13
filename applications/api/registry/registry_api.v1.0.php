<?php
namespace ANDS;
use \Exception as Exception;

class Registry_api
{
    private $ci;
    private $params;
    function __construct() {
        $this->ci =& get_instance();
    }

    function handle($method = array()) {
        $this->ci =& get_instance();
        $gets = $this->ci->input->get();

        $this->extract($method);

        if (!$this->params['submodule']) return $this->index();

        if ($handler = $this->params['submodule']) {
            return $this->$handler();
        }

        return $this->params;
    }

    function object() {

        if ($this->params['identifier']) {
            // registry/object/(:id)
            return array(
                $this->record_api()
            );
        } else {
            // registry/object
        }
        return $this->params;
    }

    function record_api() {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $record = $this->ci->ro->getByID($this->params['identifier']);

        if (!$record) throw new Exception('No record with ID '.$this->params['identifier'].' found');

        $result = array();
        $result['registry_object'] = [
            'id' => $record->id,
            'title' => $record->title
        ];
        return $result;
    }

    function extract($method) {
        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : false,
            'identifier' => isset($method[2]) ? $method[2] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
        );
    }

    function index() {
        return array(
            'Registry Index'
        );
    }

    function status() {
        return ['status returned'];
    }
}