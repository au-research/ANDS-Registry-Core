<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Maintenance Dashboard
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
class Maintenance extends MX_Controller {

	
	public function index(){
		acl_enforce('REGISTRY_STAFF');
		$data['title'] = 'ARMS Maintenance';
		$data['small_title'] = '';
		$data['scripts'] = array('maintenance');
		$data['js_lib'] = array('core', 'prettyprint', 'dataTables');

		$this->load->view("maintenance_index", $data);
	}

	public function init()
	{
		acl_enforce('REGISTRY_STAFF');
		$this->load->library('importer');
		$slogTitle =  'Import from URL completed successfully'.NL;	
		$elogTitle = 'An error occurred whilst importing from the specified URL'.NL;
		$log = 'IMPORT LOG' . NL;
		//$log .= 'URI: ' . $this->input->post('url') . NL;
		$log .= 'Harvest Method: Direct import from URL' . NL;
		$this->load->model('data_source/data_sources', 'ds');

		$data_source = $this->ds->getByKey($this->config->item('example_ds_key'));

		if (!$this->config->item('example_ds_key'))
		{
			echo "Example DataSource Key is required to complete the task" .NL;
			return;
		}

		if (!$data_source)
		{
			$data_source = $this->ds->create($this->config->item('example_ds_key'), url_title($this->config->item('example_ds_title')));
			$data_source->setAttribute('title', $this->config->item('example_ds_title'));
			$data_source->setAttribute('record_owner', 'SYSTEM');
			$data_source->save();
			$data_source->updateStats();
			$data_source = $this->ds->getByKey($this->config->item('example_ds_key'));
		}
		
		$sampleRecordUrls = array('http://services.ands.org.au/documentation/rifcs/1.5/examples/eg-collection-1.xml',
			'http://services.ands.org.au/documentation/rifcs/1.5/examples/eg-party-1.xml',
			'http://services.ands.org.au/documentation/rifcs/1.5/examples/eg-service-1.xml',
			'http://services.ands.org.au/documentation/rifcs/1.5/examples/eg-activity-1.xml');

		$xml = '';
		foreach($sampleRecordUrls as $recUrl){
			$xml .= unWrapRegistryObjects(file_get_contents($recUrl));			
		}

		$this->importer->setXML(wrapRegistryObjects($xml));
		$this->importer->setDatasource($data_source);
		$this->importer->commit(false);
		$this->importer->finishImportTasks();
		$data_source->updateStats();

		if ($error_log = $this->importer->getErrors())
		{
			$log .= $elogTitle.$log.$error_log;
			$data_source->append_log($log, HARVEST_ERROR ,"HARVEST_ERROR");
		}
		//else{
		$log .= $slogTitle.$log.$this->importer->getMessages();
		$data_source->append_log($log, HARVEST_INFO,"HARVEST_INFO");

		header('Location: '.registry_url('data_source/manage_records/'.$data_source->id));
		exit();

	}

	function getStat(){
		acl_enforce('REGISTRY_STAFF');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model('maintenance_stat', 'mm');
		$data['totalCountDB'] = $this->mm->getTotalRegistryObjectsCount('db');
		$data['totalCountDBPublished'] = $this->mm->getTotalRegistryObjectsCount('db', '*', 'PUBLISHED');
		$data['totalCountSOLR'] = $this->mm->getTotalRegistryObjectsCount('solr');
		$data['notIndexedArray'] = array_diff($this->mm->getAllIDs('db', 'PUBLISHED'), $this->mm->getAllIDs('solr'));
		$data['notIndexed'] = sizeof($data['notIndexedArray']);
		echo json_encode($data);
	}

	function getDataSourcesStat(){
		acl_enforce('REGISTRY_STAFF');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model("data_source/data_sources","ds");
		$this->load->model('maintenance_stat', 'mm');
		$dataSources = $this->ds->getAll(0,0);//get everything

		//get all data_source_count
		$this->load->library('solr');
		$this->solr->setOpt('q', '*:*');
		$this->solr->setFacetOpt('field', 'data_source_id');
		$this->solr->setFacetOpt('limit', '9999');
		$this->solr->executeSearch();
		$data_sources_indexed_count = $this->solr->getFacetResult('data_source_id');

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;
			$item['totalCountDB'] = $this->mm->getTotalRegistryObjectsCount('db', $ds->id); //kinda bad but ok for now
			$item['totalCountDBPUBLISHED'] = $this->mm->getTotalRegistryObjectsCount('db', $ds->id, 'PUBLISHED');
			//$item['totalCountSOLR'] = $this->mm->getTotalRegistryObjectsCount('solr', $ds->id); bad bad query
			if(isset($data_sources_indexed_count[$ds->id])){
				$item['totalCountSOLR'] = $data_sources_indexed_count[$ds->id];
			}else{
				$item['totalCountSOLR'] = 0;
			}
			$item['totalMissing'] =  $item['totalCountDBPUBLISHED'] - $item['totalCountSOLR'];
			array_push($items, $item);
		}
		$data['dataSources'] = $items;
		echo json_encode($data);
	}

	function enrichDS($data_source_id){//TODO: XXX
		acl_enforce('REGISTRY_STAFF');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');

		$ids = $this->ro->getIDsByDataSourceID($data_source_id);
		if($ids)
		{

			/* TWO-STAGE ENRICH */
			foreach($ids as $ro_id){
				try{
					$ro = $this->ro->getByID($ro_id);
					if($ro->getRif()){
						$ro->addRelationships();
						unset($ro);
						gc_collect_cycles();
						clean_cycles();
					}
				}catch (Exception $e){
					echo "<pre>error in: $e" . nl2br($e->getMessage()) . "</pre>" . BR;
				}
			}

			foreach($ids as $ro_id){
				try{
					$ro = $this->ro->getByID($ro_id);
					if($ro->getRif()){
						$ro->update_quality_metadata();
						$ro->enrich();
						unset($ro);
						gc_collect_cycles();
						clean_cycles();
					}
				}catch (Exception $e){
					echo "<pre>error in: $e" . nl2br($e->getMessage()) . "</pre>" . BR;
				}
			}
		}
	}

	/**
	 * web service for maintenance, this will index a data source
	 * @param  int $data_source_id 
	 * @return json result
	 */
	function indexDS($data_source_id, $logit = false){
		acl_enforce('REGISTRY_STAFF');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$data = array();
		$data['data_source_id']=$data_source_id;
		$data['error']='';

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->library('solr');

		$ids = $this->ro->getIDsByDataSourceID($data_source_id, false, PUBLISHED);

		$i = 0;
		$response = '';
		$errors = '';
		$solrXML = '';
		if($ids)
		{
			
			$chunkSize = 400; 
			$arraySize = sizeof($ids);
			for($i = 0 ; $i < $arraySize ; $i++)
			{
				$roId = $ids[$i];	
				try{
					$ro = $this->ro->getByID($roId);
					if($ro)
					{
						$solrXML .= $ro->transformForSOLR();
						if(($i % $chunkSize == 0 && $i != 0) || $i == ($arraySize -1))
						{
							$result = $this->solr->addDoc("<add>".$solrXML."</add>");
							$response .= $result.NL;
							$this->solr->commit();
							$solrXML = '';
						}
					}
				}
				catch (Exception $e)
				{
					$errors .= nl2br($e).NL;
				}
			}

			$data['results'] = $response;
			$data['errors'] = $errors;
			$data['totalAdded'] = $i;
		}
		if(!$logit)
			echo json_encode($data);
		else
			return json_encode($data);
	}

	function clearDS($data_source_id, $logit = false){
		acl_enforce('REGISTRY_STAFF');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->library('solr');
		$data['result'] = $this->solr->clear($data_source_id);
		if(!$logit)
			echo json_encode($data);
		else
			return json_encode($data);
	}
	
	function clearAll(){
		acl_enforce('REGISTRY_STAFF');
		$data = array();
		$data['logs'] = '';
		$this->load->model('data_source/data_sources', 'ds');
		$dsIds = $this->ds->getAll(0);
		$data_sources = $this->ds->getAll(0);
		foreach($data_sources as $ds){
			$data['logs'] .= $this->clearDS($ds->id, true);
		}
		echo json_encode($data);
	}

	function indexAll(){
		acl_enforce('REGISTRY_STAFF');
		$data = array();
		$data['logs'] = '';
		$this->load->model('data_source/data_sources', 'ds');
		$data_sources = $this->ds->getAll(0);
		foreach($data_sources as $ds){
			$data['logs'] .= $this->indexDS($ds->id, true);
		}
		echo json_encode($data);
	}

	function enrichAll(){
		acl_enforce('REGISTRY_STAFF');
		$data = array();
		$data['logs'] = '';
		$this->load->model('data_source/data_sources', 'ds');
		$data_sources = $this->ds->getAll(0);
		foreach($data_sources as $ds){
			$data['logs'] .= $this->enrichDS($ds->id);
		}
		echo json_encode($data);
	}

	function enrichMissing(){
		acl_enforce('REGISTRY_STAFF');
		$data['logs'] = '';
		$this->load->model('registry_object/registry_objects', 'ro');
		$unenriched = $this->ro->getUnEnriched();
		foreach($unenriched->result() as $u){
			$ro = $this->ro->getByID($u->registry_object_id);
			$ro->enrich();
			$data['logs'] .= $ro->id.' ';
		}
		echo json_encode($data);
	}

	/**
	 * Clean the Index of records that doesn't exist
	 * @return [type] [description]
	 */
	function cleanNotExist(){
		acl_enforce('REGISTRY_STAFF');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model('maintenance_stat', 'mm');
		$this->load->model('registry_object/registry_objects', 'ro');

		$solr_ids = $this->mm->getAllIDs('solr');
		$data['logs'] = '';

		//collect the unset array
		$unset = array();
		foreach($solr_ids as $id){
			try{
				$ro = $this->ro->getByID($id);
				if(!$ro || !$ro->getRif() || $ro->status != 'PUBLISHED'){
					array_push($unset, $id);
				}
				unset($ro);
			}catch (Exception $e){
				echo "<pre>error in: $e" . nl2br($e->getMessage()) . "</pre>" . BR;
			}
		}
		
		//actually delete them from the index
		$this->load->library('solr');
		foreach($unset as $id){
			$this->solr->deleteByID($id);
			$data['logs'] .= $id.' deleted from index | ';
		}

		echo json_encode($data);
	}

	/**
	 * Sync a single registry object by key or by id
	 * @return json
	 */
	function sync(){
		acl_enforce('REGISTRY_STAFF');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->library('solr');

		$use = 'id';
		$idkey = $this->input->post('idkey');
		
		$ro = $this->ro->getByID($idkey);
		if(!$ro) {
			$ro = $this->ro->getAllByKey($idkey);
			$use = 'keys';
		}
		if(!$ro) {
			$data['status']='error';
			$data['message'] = '<i class="icon icon-remove"></i> No Registry Object Found!';
		}else{
			if($use=='id'){
				$ro->enrich();
				$solrXML = $ro->transformForSOLR();
				$this->solr->addDoc("<add>".$solrXML."</add>");
				$this->solr->commit();
			}elseif($use=='keys'){
				foreach($ro as $r){
					$r->enrich();
					$solrXML = $r->transformForSOLR();
					$this->solr->addDoc("<add>".$solrXML."</add>");
					$this->solr->commit();
				}
			}
			$data['status'] = 'success';
			$data['message'] = '<i class="icon icon-ok"></i> Done!';
		}
		echo json_encode($data);
	}


	/**
	 * @ignore
	 */
	public function __construct(){
		parent::__construct();
	}
	
}

/* End of file vocab_service.php */
/* Location: ./application/models/vocab_services/controllers/vocab_service.php */