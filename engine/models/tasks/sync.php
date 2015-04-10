<?php

/**
 * Authenticator for AAF Rapid Connect
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
require_once('engine/models/task.php');
class Sync extends Task {

	private $target = false; //ds or ro
	private $target_id = false;
	private $chunkSize = 100;
	private $chunkPos = 0;
	private $mode = 'sync';
	
	function load_params($task) {
		parse_str($task['params'], $params);
		$this->target = $params['type'] ? $params['type'] : false;
		$this->target_id = $params['id'] ? $params['id'] : false;

		// ulog('target ids'. $this->target_id);

		if(isset($params['chunkPos'])) {
			$this->chunkPos = $params['chunkPos'];
		} else {
			$this->mode = 'analyze';
		}

		$this->load->model('registry/registry_object/registry_objects', 'ro');
		$this->load->model('registry/data_source/data_sources', 'ds');
		$this->load->library('solr');
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
		} else if($this->target=='solr_query') {
			if ($this->target_id) {
				$this->analyze_solr_query($this->target_id, 'index');
			}
		} else if($this->target=='index_ro') {
			$list = explode(',', $this->target_id);
			$this->index($list);
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
		$this->log('[success][task:queued][size:'.$data['total'].']');
	}

	function analyze_solr_query($query) {
		$ci =& get_instance();
		$ci->load->library('solr');
		$ci->solr
			->setOpt('rows', '50000')
			->setOpt('fl', 'id')
			->setOpt('q', $query);
		$result = $this->solr->executeSearch(true);

		$data['total'] = sizeof($result['response']['docs']);
		$data['chunkSize'] = $this->chunkSize;
		$data['numChunk'] = ceil(($data['chunkSize'] < $data['total'] ? ($data['total'] / $data['chunkSize']) : 1));

		$ids = array();
		foreach($result['response']['docs'] as $doc) {
			array_push($ids, $doc['id']);
		}

		$chunks = array_chunk($ids, $data['chunkSize']);
		$this->load->model('task_mgr');
		foreach($chunks as $chunk) {
			$task = array(
				'name' => 'sync',
				'params' => 'type=sync&id='.implode(',', $chunk)
			);
			$this->task_mgr->add_task($task);
		}
		$this->log('[success][task:queued][size:'.$data['total'].']');
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
				// ulog('[syncing:'.$ro_id.']');
				$res = $ro->sync();
				if($res===true) {
					$this->log('[success][sync][ro_id:'.$ro_id.']');
				} else {
					$this->log('[error][error:sync][msg:'.$res.']');
				}
				// ulog('[success][sync][ro_id:'.$ro_id.']');
				unset($ro);
			} else {
				//record is not found, proceed to delete the index
				$this->solr->deleteByID($ro_id);
				$this->log('[error][notfound][ro_id:'.$ro_id.']');
				unset($ro);
			}
		} catch (Exception $e) {
			$this->log('[error] '. $e->getMessage());
		}
	}

	function index($list) {
		$docs = array();
		foreach($list as $ro_id) {
			try {
				$ro = $this->ro->getByID($ro_id);
				if($ro) {
					$docs[] = $ro->indexable_json();
				} else {
					$this->solr->deleteByID($ro_id);
					$this->log('[error][notfound][ro_id:'.$ro_id.']');
				}
				unset($ro);
			} catch (Exception $e) {
				$this->log('[error] '. $e->getMessage());
			}
		}
		$r = $this->solr->add_json(json_encode($docs));
		$this->log('[success][add_solr_msg:'.$r.']');
		$r = $this->solr->commit();
		$this->log('[success][commit_solr_msg:'.$r.'][task:index_ro][ro_id:'.implode(',', $list).']');
	}


}