<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Maintenance Dashboard
 * 
 * 
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * 
 */
class Config extends MX_Controller {

	public function index() {
		$this->get();
	}

	public function form() {
		acl_enforce('REGISTRY_STAFF');
		$data['title'] = 'Configuration';
		$data['scripts'] = array('config_app');
		$data['js_lib'] = array('core', 'angular');
		$this->load->view("config_app", $data);
	}

	public function get() {
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$data = array();

		$query = $this->db->get('configs');
		$configs = $query->result_array();

		foreach($configs as $c) {
			$data[$c['key']] = array(
				'type' => $c['type'],
				'value' => ($c['type']=='json') ? json_decode($c['value'],true) : $c['value'],
				'gb_value' => get_global_config_item($c['key'])
			);
		}

		//fill out things that are not in the database
		$fill = array('harvested_contents_path', 'solr_url', 'environment_name', 'environment_colour', 'site_admin', 'site_admin_email', 'sissvoc_url', 'shibboleth_sp');

		foreach($fill as $f){
			if(!isset($data[$f])){
				$data[$f] = array(
					'type' => 'string',
					'value' => '',
					'gb_value' => get_global_config_item($f)
				);
			}
		}

		echo json_encode($data);
	}

	public function save() {
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$data = file_get_contents("php://input");
		$data = json_decode($data, true);
		$data = $data['data'];

		if(!$data) throw new Exception('No data to save');

		$anychanged = false;
		foreach($data as $key=>$c) {
			if ($c['type']=='string') {
				if(!$anychanged && $c['value']!=get_db_config_item($key)) $anychanged = true;
				if($c['value']!=get_db_config_item($key)){
					$anychanged = true;
					set_config_item($key, $c['type'], $c['value']);
				}
			}
		}

		if(!$anychanged) {
			echo json_encode(array(
				'status' => 'OK',
				'message' => 'No configuration change detected'
			));
		} else {
			echo json_encode(array(
				'status' => 'OK',
				'message' => 'All configuration item successfully updated'
			));
		}
	}

}