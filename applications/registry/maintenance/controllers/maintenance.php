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
		// acl_enforce('REGISTRY_STAFF');
		// $data['title'] = 'ARMS Maintenance';
		// $data['small_title'] = '';
		// $data['scripts'] = array('maintenance');
		// $data['js_lib'] = array('core', 'prettyprint', 'dataTables');

		// $this->load->view("maintenance_index", $data);

		acl_enforce('REGISTRY_STAFF');
		$data['title'] = 'ARMS SyncMenu';
		$data['scripts'] = array('sync_app');
		$data['js_lib'] = array('core', 'angular', 'dataTables');
		$this->load->view("syncmenu_index", $data);
	}

	public function migrate_tags_to_r11(){
		acl_enforce('REGISTRY_STAFF');
		$this->load->model('registry_object/registry_objects', 'ro');
		$filters = array(
			'filter'=>array('tag'=>'!=')
		);
		$ros = $this->ro->filter_by($filters,100);

		$affected_ros = array();
		foreach($ros as $ro){
			if($ro->tag!=='1' && $ro->tag!=='0') array_push($affected_ros, $ro);
		}
		if(sizeof($affected_ros)==0){
			echo 'No legacy tags found!';
		}else{
			foreach($affected_ros as $ro){
				echo '<b>ID</b>: '. $ro->id.' <b>title</b>: '. $ro->title. ' <b>Tag</b>: '. $ro->tag.'<br/>';
				$tags = explode(';;', $ro->tag);
				foreach($tags as $tag){
					$ro->addTag($tag);
				}
				$ro->sync();
				echo 'Tags added correctly!';
				echo '<hr/>';
			}
		}
	}

	public function migrate_themes_to_r12(){
		acl_enforce('REGISTRY_STAFF');
		$directory = './assets/shared/theme_pages/';
		$index_file = 'theme_cms_index.json';
		$root = scandir($directory, 1);
		$this->load->helper('file');
		$result = array();
		$this->db->empty_table('theme_pages');
		foreach($root as $value){
			if($value === '.' || $value === '..') {continue;} 
			$pieces = explode(".", $value);
			if(is_file("$directory/$value")) {
				if($pieces[0].'.json'!=$index_file){
					$file = json_decode(read_file($directory.$pieces[0].'.json'), true);
					$theme_page = array(
						'title' => (isset($file['title'])?$file['title']:'No Title'),
						'slug' => (isset($file['slug'])?$file['slug']:$pieces[0]),
						'img_src'=> (isset($file['img_src'])?$file['img_src']:''),
						'description'=>(isset($file['desc'])?$file['desc']:''),
						'visible'=>(isset($file['visible'])?$file['visible']:false),
						'content'=>json_encode($file)
					);
					if(isset($file['visible']) && $file['visible']==='true'){
						$theme_page['visible'] = 1;
					}else $theme_page['visible'] = 0;
					$this->db->insert('theme_pages', $theme_page);
				}
			} 
		}
		echo 'done';
	}

	public function syncmenu(){
		acl_enforce('REGISTRY_STAFF');
		$data['title'] = 'ARMS SyncMenu';
		$data['scripts'] = array('sync_app');
		$data['js_lib'] = array('core', 'angular', 'dataTables');
		$this->load->view("syncmenu_index", $data);
	}

	public function init(){
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
			$data_source->setAttribute('record_owner', 'superuser');
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

	function getDataSourceList($detailed=false){
		acl_enforce('REGISTRY_STAFF');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model("data_source/data_sources","ds");
		$this->load->model('maintenance_stat', 'mm');

		$dataSources = $this->ds->getAll(0,0);
		$items = array();

		if($detailed){
			//get all data_source_count
			$this->load->library('solr');
			$this->solr->setOpt('q', '*:*');
			$this->solr->setFacetOpt('field', 'data_source_id');
			$this->solr->setFacetOpt('limit', '9999');
			$this->solr->executeSearch();
			$data_sources_indexed_count = $this->solr->getFacetResult('data_source_id');
		}

		foreach($dataSources as $ds){
			$item = array();
			$item = array(
				'id'=>$ds->id,
				'key'=>$ds->key,
				'title'=>$ds->title,
				'total_published'=>(int) $ds->count_PUBLISHED
			);
			if($detailed){
				// $item['totalCountDB'] = $this->mm->getTotalRegistryObjectsCount('db', $ds->id); //kinda bad but ok for now
				// $item['totalCountDBPUBLISHED'] = $this->mm->getTotalRegistryObjectsCount('db', $ds->id, 'PUBLISHED');
				if(isset($data_sources_indexed_count[$ds->id])){
					$item['total_indexed'] = $data_sources_indexed_count[$ds->id];
				}else{
					$item['total_indexed'] = 0;
				}
				$item['total_missing'] = $item['total_published'] - $item['total_indexed'];
			}
			// $item = array($ds->id, $ds->title, $ds->count_PUBLISHED,'<button class="btn">Sync</button>');
			array_push($items, $item);
		}
		echo json_encode($items);
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

	function getDataSourceStat($ds_id){
		acl_enforce('REGISTRY_STAFF');
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$this->load->library('solr');
		$this->solr->setOpt('q', '*:*');
		$this->solr->setOpt('rows', '0');
		$this->solr->setFacetOpt('field', 'data_source_id');
		$this->solr->setFacetOpt('limit', '9999');
		$this->solr->executeSearch();
		$data_sources_indexed_count = $this->solr->getFacetResult('data_source_id');

		$this->load->model("data_source/data_sources","ds");
		$this->load->model('maintenance_stat', 'mm');
		$ds = $this->ds->getByID($ds_id);

		$result['ds'] = $ds;
		$result['totalCountDB'] = $this->mm->getTotalRegistryObjectsCount('db', $ds->id); //kinda bad but ok for now
		$result['totalCountDBPUBLISHED'] = $this->mm->getTotalRegistryObjectsCount('db', $ds->id, 'PUBLISHED');
		if(isset($data_sources_indexed_count[$ds->id])){
			$result['totalCountSOLR'] = $data_sources_indexed_count[$ds->id];
		}else{
			$result['totalCountSOLR'] = 0;
		}
		$result['totalMissing'] =  $result['totalCountDBPUBLISHED'] - $result['totalCountSOLR'];
		echo json_encode($result);
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
		$this->load->library('solr');
		$data['logs'] .= $this->solr->clear();
		echo json_encode($data);
	}

	function indexAll($print=false){
		acl_enforce('REGISTRY_STAFF');
		$data = array();
		$data['logs'] = '';
		$this->load->model('data_source/data_sources', 'ds');
		$data_sources = $this->ds->getAll(0);
		foreach($data_sources as $ds){
			$data['logs'] .= $this->indexDS($ds->id, true);
			if ($print)
			{
				echo $data['logs'];
				$data['logs'] = '';
				flush();
			}
		}
		if (!$print)
		{
			echo json_encode($data);
		}
	}

	function smartSync($step = 0, $start = 0){
		$limit = 400;
		$this->load->model('data_source/data_sources', 'ds');
		$result = array();
		if($step==0){
			$result = $this->ds->getGroupsBySizeLimit($limit);
			echo json_encode($result);	
		}else if($step==1){
			$result = $this->ds->getGroupsBySizeLimit($limit);
			$total = sizeof($result['small']);
			$current = 1;
			foreach($result['small'] as $ds){
				if($current >= $start){
					echo '<b>'.$ds['data_source_id'].' ('.$current.'/'.$total.')</b> Count: '.$ds['count'].'<br/>';
					$this->flush_buffers();
					var_dump($this->smartSyncDS($ds['data_source_id']));
					echo '<hr/>';
					$this->flush_buffers();
				}
				$current++;
			}
		}else if($step==2){
			$result = $this->ds->getGroupsBySizeLimit($limit);
			$total = sizeof($result['large']);
			$current = 1;
			foreach($result['large'] as $ds){
				if($current >= $start){
					echo '<b>'.$ds['data_source_id'].' ('.$current.'/'.$total.')</b> Count: '.$ds['count'].'<br/>';
					$this->flush_buffers();
					var_dump($this->smartSyncDS($ds['data_source_id']));
					echo '<hr/>';
					$this->flush_buffers();
				}
				$current++;
			}
		}
	}

	function smartAnalyze($task='sync', $data_source_id){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');

		$ds = $this->ds->getByID($data_source_id);
		$keys = $this->ro->getKeysByDataSourceID($data_source_id, false, 'PUBLISHED');

		$chunkSize = 50;
		if($task=='index') $chunkSize = 200;

		$data = array();
		$data['total'] = sizeof($keys);
		$data['chunkSize'] = $chunkSize;
		$data['numChunk'] = ceil(($chunkSize < $data['total'] ? ($data['total'] / $chunkSize) : 1));

		echo json_encode($data);
	}

	function smartSyncDS($task='sync', $data_source_id=false, $chunk_pos=false){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		
		$this->benchmark->mark('start');

		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');

		$chunkSize = 50;
		if($task=='index') $chunkSize = 200;

		if(!$data_source_id) throw new Exception ('Data Source ID must be provided as first param');
		if(!$chunk_pos || $chunk_pos == 0) throw new Exception ('Chunk Position must be provided as second param');

		$ds = $this->ds->getByID($data_source_id);
		$offset = ($chunk_pos-1) * $chunkSize;
		$limit = $chunkSize;
		$keys = $this->ro->getKeysByDataSourceID($data_source_id, false, 'PUBLISHED', $offset, $limit);
		$totalEnrichTime = 0; $totalIndexTime = 0; $allErrors = array(); $allSOLRXML = '';
		$results = array();
		foreach($keys as $key){
			$result = array();
			$ro = $this->ro->getPublishedByKey($key);
			$error = array();
			if($ro){
				$this->benchmark->mark('enrich_ro_start');
				//enrich
				if($task=='sync' || $task=='full_enrich'){
					try{
						$ro->addRelationships();
					}catch(Exception $e){
						array_push($error, $e->getMessage());
					}

					try{
						$ro->update_quality_metadata();
					}catch(Exception $e){
						array_push($error, $e->getMessage());
					}
				}

				if($task=='sync' || $task=='full_enrich' || $task=='fast_enrich'){
					try{
						$ro->enrich();
					}catch(Exception $e){
						array_push($error, $e->getMessage());
					}
				}

				$this->benchmark->mark('enrich_ro_end');

				//index
				
				if($task=='sync' || $task=='index'){
					try{
						$solrXML = $ro->transformForSOLR();
						$allSOLRXML .= $solrXML;
					}catch(Exception $e){
						array_push($error, $e->getMessage());
					}
				}

				$totalEnrichTime += $this->benchmark->elapsed_time('enrich_ro_start', 'enrich_ro_end');
				$result[$key] = array(
					'enrichTime' => $this->benchmark->elapsed_time('enrich_ro_start', 'enrich_ro_end'),
				);
				
			}else{
				$result[$key] = 'Not Found';
			}
			if(sizeof($error)>0) {
				$result[$key]['error'] = $error;
				array_push($allErrors, array(
					'key' => $key,
					'error_msg'=>$error
				));
			}
			array_push($results, $result);
			unset($ro);
		}

		$this->benchmark->mark('index_start');

		if($task=='clear'){
			$this->solr->clear($data_source_id);
		}

		if($task=='sync' || $task=='index'){
			$this->solr->addDoc('<add>'.$allSOLRXML.'</add>');
			$this->solr->commit();
		}
		
		$this->benchmark->mark('index_end');
		$totalIndexTime = $this->benchmark->elapsed_time('index_start', 'index_end');
		
		$this->benchmark->mark('finish');

		$data['benchMark'] = array(
			'chunkSize'=>$chunkSize,
			'enrichTime'=>$totalEnrichTime,
			'indexTime'=>$totalIndexTime,
			'totalTime'=>(float) $this->benchmark->elapsed_time('start', 'finish'),
			'avgTimePerRecord' => (float) $this->benchmark->elapsed_time('start', 'finish') / $chunkSize
		);
		$data['errors'] = $allErrors;
		$data['results'] = $results;

		echo json_encode($data);
	}

	function smartSyncDS2($data_source_id, $print=false, $offset=0){
		$this->load->library('importer');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');

		//load
		$ds = $this->ds->getByID($data_source_id);
		$keys = $this->ro->getKeysByDataSourceID($data_source_id, false, PUBLISHED);

		if($offset!=0) $keys = array_slice($keys, $offset);

		if($print){
			echo 'count: '.sizeof($keys).'<br/>';
		}

		$this->importer->_reset();
		$this->importer->runBenchMark = true;
		$this->importer->setDataSource($ds);
		$this->importer->addToAffectedList($keys);

		$this->importer->finishImportTasks();
		if($print){
			var_dump($this->importer->getBenchMarkLogArray());
		}else{
			return $this->importer->getBenchMarkLogArray();
		}
	}

	function flush_buffers(){ 
		ob_flush(); 
		flush(); 
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
		set_exception_handler('json_exception_handler');

		$this->load->model('registry_object/registry_objects', 'ro');
		$this->load->library('solr');

		$use = 'id';
		$idkey = $this->input->post('idkey');
		if(!$idkey){
			$data = file_get_contents("php://input");
			$array = json_decode($data, true);
			$idkey = $array['idkey'];
		}

		$ro = $this->ro->getByID($idkey);
		if(!$ro) {
			$ro = $this->ro->getAllByKey($idkey);
			$use = 'keys';
		}
		if(!$ro) {
			$ro = $this->ro->getBySlug($idkey);
			$use = 'slug';
		}

		$data = array();

		if(!$ro) {
			$data['status']='error';
			$data['message'] = '<i class="icon icon-remove"></i> No Registry Object Found!';
		}else{
			if($use=='id'){
				if($msg = $ro->sync()!=true){
					$data['status'] = 'error';
					$data['message'] = $msg;
				}

			}else if($use=='keys'){
				foreach($ro as $r){
					if($msg = $ro->sync()!=true){
						$data['status'] = 'error';
						$data['message'] = $msg;
					}
				}
			}else if($use=='slug'){
				if($msg = $ro->sync()!=true){
					$data['status'] = 'error';
					$data['message'] = $msg;
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