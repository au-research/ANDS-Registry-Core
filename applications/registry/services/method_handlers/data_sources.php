<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class Data_sourcesMethod extends MethodHandler {

	public $ds;
	public $ci;

	function handle($params = ''){
		$this->params = $params;

        $id = isset($params[1]) ? $params[1] : false;
        $method1 = isset($params[2]) ? $params[2] : false;
        $this->ci =& get_instance();
        $this->ci->benchmark->mark('code_start');
        $result = array();

        if ($this->ci->user->isLoggedIn()) {
        	$result['user'] = array(
        		'logged_in' => true,
        		'auth_method' => $this->ci->user->authMethod(),
        		'identifier' => $this->ci->user->localIdentifier(),
        		'name' => $this->ci->user->name()
        	);
        }


        $ids = explode('-', $id);

        $this->ci->load->model('data_source/data_sources', 'ds');
        foreach($ids as $id) {
        	if($id) {
        		$result[$id] = array();
        		$this->ds = $this->ci->ds->getByID($id);
        		$method1s = explode('-', $method1);
        		foreach($method1s as $m1){
        			switch($m1) {
        				case 'core': $result[$id][$m1] = $this->core_handler(); break;
        				case 'groups': $result[$id][$m1] = $this->groups_handler(); break;
        			}
        		}
        	}
        }

        $this->ci->benchmark->mark('code_end');
        $benchmark = array(
            'elapsed' => $this->ci->benchmark->elapsed_time('code_start', 'code_end'),
            'memory_usage' => $this->ci->benchmark->memory_usage()
        );
        return $this->formatter->display($result, $benchmark);
	}

	private function core_handler() {
		$params = array('title', 'record_owner', 'key');
		$result['id'] = isset($this->ds) ? $this->ds->id : false;
		foreach($params as $par) {
			if($this->ds) {
				$result[$par] = $this->ds->getAttribute($par);
			}
		}
		return $result;
	}

	private function groups_handler() {
		$groups = $this->ds->get_groups();
		return $groups;
	}
}