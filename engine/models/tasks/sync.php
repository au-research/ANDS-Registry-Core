<?php

/**
 * Authenticator for AAF Rapid Connect
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
require_once('engine/models/task.php');
class Sync extends Task {

	private $target = false; //ds or ro
	private $target_id = false;
	private $chunkSize = 50;
	private $chunkPos = 0;
	private $mode = 'sync';
	
	function load_params($task) {
		parse_str($task['params'], $params);
		$this->target = $params['type'] ? $params['type'] : false;
		$this->target_id = $params['id'] ? $params['id'] : false;

		if(isset($params['chunkPos'])) {
			$this->chunkPos = $params['chunkPos'];
		} else {
			$this->mode = 'analyze';
		}

		$this->load->model('registry/registry_object/registry_objects', 'ro');
		$this->load->model('registry/data_source/data_sources', 'ds');
	}

	function run_task() {
		if($this->target=='ro') {
			$list = explode(',', $this->target_id);
			foreach($list as $ro_id){
				$this->sync_ro($ro_id);
			}
		} else if($this->target=='ds') {
			$list = explode(',', $this->target_id);
			foreach($list as $ds_id) {
				if($this->mode=='analyze') {
					$this->analyze_ds($ds_id);
				} else if($this->mode=='sync') {
					$this->sync_ds($ds_id);
				}
			}
		}
	}

	function analyze_ds($ds_id) {
		$ids = $this->ro->getIDsByDataSourceID($ds_id, false, 'PUBLISHED');
		$data['total'] = sizeof($ids);
		$data['chunkSize'] = $this->chunkSize;
		$data['numChunk'] = ceil(($this->chunkSize < $data['total'] ? ($data['total'] / $this->chunkSize) : 1));

		$this->load->model('task_mgr');
		for ($i=1;$i<=$data['numChunk'];$i++) {
			$task = array(
				'name' => 'sync',
				'params' => 'type=ds&id='.$ds_id.'&chunkPos='.$i,
			);
			$this->task_mgr->add_task($task);
		}
	}

	function sync_ds($ds_id) {
		$offset = ($this->chunkPos-1) * $this->chunkSize;
		$limit = $this->chunkSize;
		$ids = $this->ro->getIDsByDataSourceID($ds_id, false, 'PUBLISHED', $offset, $limit);
		$solr_docs = array();
		foreach($ids as $ro_id) {
			try{
				$ro = $this->ro->getByID($ro_id);
				if($ro) {
					$ro->processIdentifiers();
					$ro->addRelationships();
					$ro->update_quality_metadata();
					$ro->enrich();
					$solr_docs[] = $ro->indexable_json();
					unset($ro);
				} else {
					$this->log('[error][notfound][ro_id:'.$ro_id.']');
				}
			} catch (Exception $e) {
				$this->log('[error][sync][ds_id:'.$ds_id.'][message:'.$e->getMessage().'][ro_id:'.$ro_id.']');
			}
		}

		try {
			$this->load->library('solr');
			$this->solr->add_json(json_encode($solr_docs));
			$this->solr->commit();
		} catch (Exception $e) {
			$this->log('[error][sync][ds_id:'.$ds_id.'][index:'.$e->getMessage().']');
		}

		$this->log('[success][sync][ds_id:'.$ds_id.'][chunk:'.$this->chunkPos.']');
	}

	function sync_ro($ro_id) {
		try {
			$ro = $this->ro->getByID($ro_id);
			if($ro) {
				$ro->sync();
				$this->log('[success][sync][ro_id:'.$ro_id.']');
				unset($ro);
			} else {
				$this->log('[error][notfound][ro_id:'.$ro_id.']');
			}
		} catch (Exception $e) {
			$this->log('[error] '. $e->getMessage());
		}
	}


}