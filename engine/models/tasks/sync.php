<?php

/**
 * Authenticator for AAF Rapid Connect
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
require_once('engine/models/task.php');
class Sync extends Task {

	private $target = false; //ds or ro
	private $target_id = false;
	
	function load_params($task) {
		parse_str($task['params'], $params);
		$this->target = $params['type'] ? $params['type'] : false;
		$this->target_id = $params['id'] ? $params['id'] : false;
	}

	function run_task() {
		if($this->target=='ro') {
			$this->load->model('registry/registry_object/registry_objects', 'ro');
			$list = explode(',', $this->target_id);
			foreach($list as $ro_id){
				try {
					$ro = $this->ro->getByID($ro_id);
					if($ro) {
						$ro->sync();
						$this->log('[success][sync][ro_id:'.$ro_id.']');
						unset($ro);
					} else $this->log('[error][notfound][ro_id:'.$ro_id.']');
				} catch (Exception $e) {
					$this->log('[error] '. $e->getMessage());
				}
			}
		} else if($this->target=='ds') {
			$this->load->model('registry/data_source/data_sources', 'ds');
			$this->load->model('registry/registry_object/registry_objects', 'ro');
			$list = explode(',', $this->target_id);
			foreach($list as $ds_id) {
				try {
					$ds = $this->ds->getByID($ds_id);
					if($ds) {
						$ids = $this->ro->getIDsByDataSourceID($ds_id, false);
						foreach($ids as $ro_id) {
							$ro = $this->ro->getByID($ro_id);
							if($ro) {
								$ro->sync();
							} else $this->log('[error][notfound][ro_id:'.$ro_id.']');
						}
						$this->log('[success][sync][ro_id:'.$ds_id.']');
					} else $this->log('[error][notfound][ds_id:'.$ds_id.']');
				} catch (Exception $e) {
					$this->log('[error] '. $e->getMessage());
				}
			}
		}
	}


}