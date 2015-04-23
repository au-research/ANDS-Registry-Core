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
		$data['title'] = 'Registry Status';
		$data['scripts'] = array('status_app');
		$data['js_lib'] = array('core', 'angular');
		$this->load->view("maintenance_dashboard", $data);
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
		echo 'Done';
	}

	public function migrate_tags_to_r12(){
		acl_enforce('REGISTRY_STAFF');
		$this->db->select('distinct(tag), type')->from('registry_object_tags');
		$tags = $this->db->get();
		$tags = $tags->result_array();
		foreach($tags as $t){
			$tag = array(
				'name' => $t['tag'],
				'type' => $t['type']
			);
			$this->db->insert('tags', $tag);
		}
		echo 'Done';
	}

	public function migrate_slugs_to_r13($commit = false) {
		acl_enforce('REGISTRY_STAFF');
	
		if(!$commit){
			$result = $this->db->query('SELECT slug,registry_object_id FROM dbs_registry.registry_objects WHERE CHAR_LENGTH(SLUG) > 60;');
			echo 'There are '.$result->num_rows().' slugs that are longer than 60 characters <br/>';
			$result = $this->db->query('select slug,registry_object_id from dbs_registry.url_mappings where registry_object_id IS NULL and slug in(select slug from registry_objects);');
			echo 'There are '.$result->num_rows().' orphaned slugs <br/>';
			$result = $this->db->select('slug, registry_object_id')->from('url_mappings')->where('registry_object_id', NULL)->get();
			echo 'There are '.$result->num_rows(). ' bad slugs <br/>';
			echo 'Run migrate_slugs_to_r13/true to commit fixing orphaned slugs and delete bad slugs';
		} else {
			ob_start();
			ob_implicit_flush(1);

			$this->load->model('registry_object/registry_objects', 'ro');

			//fix orphaned slugs, giving them a registry object id
			$result = $this->db->query('select slug,registry_object_id from dbs_registry.url_mappings where registry_object_id IS NULL and slug in(select slug from registry_objects);');
			$result_array = $result->result_array();
			if($result->num_rows()==0) {
				echo 'There are no orphaned slug. <br/>';
			} else echo 'There are '. $result->num_rows(). ' orphaned slug. Fixing. <br/>';
			foreach($result->result_array as $r){
				$ro = $this->ro->getBySlug($r['slug']);
				if($ro){
					$result = $this->db->update('url_mappings', array(
						'registry_object_id'=>$ro->id
					), array('slug'=>$r['slug']));
					if($result){
						echo 'success: '. $r['slug']. ' updated to '.$ro->id.'<br/>';
					} else {
						'failed: (cant update):'. $r['slug'].'<br/>';
					}
				} else {
					echo 'failed (no record): '.$r['slug'].'<br/>';
				}
				unset($ro);
				ob_flush();flush();
			}

			//delete bad slug
			$result = $this->db->select('slug, registry_object_id')->from('url_mappings')->where('registry_object_id', NULL)->get();
			if($result->num_rows()==0) {
				echo 'There are no bad slug. <br/>';
			} else {
				echo 'There are '. $result->num_rows(). ' bad slug. Removing. <br/>';
				$result = $this->db->delete('url_mappings', array('registry_object_id'=>NULL));
				if($result){
					echo 'success<br/>';
				} else {
					echo 'failed<br/>'; 
				}
			}
			

			//generating new slugs
			$result = $this->db->query('SELECT slug,registry_object_id FROM dbs_registry.registry_objects WHERE CHAR_LENGTH(SLUG) > 60;');
			echo 'There are '.$result->num_rows().' slugs that are longer than 60 characters <br/>';
			$i=1;
			foreach($result->result_array() as $r){
				echo $i.' ';
				$ro = $this->ro->getByID($r['registry_object_id']);
				if($ro){
					$oldSlug = $ro->slug;
					$newSlug = $ro->generateSlug();
					if($newSlug){
						echo 'success:'.$r['slug'].' -> '. $newSlug.'<br/>';
					}
				} else {
					echo 'failed (no record): '.$r['slug'].'<br/>';
				}
				$i++;
				unset($ro);
				ob_flush();flush();
			}
			ob_end_flush(); 

		}

		// $result = $this->db->select('slug, registry_object_id')->from('url_mappings')->where('registry_object_id', NULL)->get();
		
	}

	public function migrate_ds_to_r13() {
		acl_enforce('REGISTRY_STAFF');
		set_exception_handler('json_exception_handler');
		$this->load->model('data_source/data_sources', 'ds');
		$all_ds = $this->ds->getAll(0,0);
		foreach($all_ds as $ds){
			$ds->title = $ds->title;
			$ds->record_owner = $ds->record_owner;
			try {
				//fix Title
				$ds->_initAttribute('title', $ds->title, TRUE);
				$ds->_initAttribute('record_owner', $ds->record_owner, TRUE);

				if($ds->harvest_method=='GET') $ds->harvest_method = 'GETHarvester';
				if($ds->harvest_method=='PMH' || $ds->harvest_method=='RIF') $ds->harvest_method = 'PMHHarvester';

				$ds->save();

				$this->db->delete('data_source_attributes', array('data_source_id'=>$ds->id, 'attribute'=>'title'));
				$this->db->delete('data_source_attributes', array('data_source_id'=>$ds->id, 'attribute'=>'record_owner'));

			} catch (Exception $e) {
				throw new Exception($e);
			}
		}
		echo 'done';
	}

	public function migrate_harvest_reqs_to_r13() {
		acl_enforce('REGISTRY_STAFF');
		set_exception_handler('json_exception_handler');

		$old_harvest_requests = $this->db->get('harvest_requests');
		if($old_harvest_requests->num_rows() > 0) {
			foreach($old_harvest_requests->result() as $orq){
				$row = array(
					'data_source_id' => $orq->data_source_id, 
					'status' => 'SCHEDULED',
					'next_run' => date( 'Y-m-d H:i:s', strtotime($orq->next_harvest)) ,
					'mode' => 'HARVEST',
					'batch_number' => strtoupper(sha1(strtotime($orq->next_harvest)))
				);
				$harvest = $this->db->insert('harvests', $row);
				if(!$harvest){
					echo $this->db->_error_message();die();
				}
			}
		}
		echo 'done';
	}

	public function migrate_content_path_to_r13() {
		acl_enforce('REGISTRY_STAFF');
		set_exception_handler('json_exception_handler');
		if($this->input->get('val')){
			set_config_item('harvested_contents_path', 'string', $this->input->get('val'));
			echo 'done';
		} else {
			throw new Exception('val required');
		}
	}

	public function migrate_ds_attr_to_r13(){
		acl_enforce('REGISTRY_STAFF');
		set_exception_handler('json_exception_handler');
		$this->db->where('value', 't');
		$query = $this->db->update('data_source_attributes', array('value'=>DB_TRUE));
		if($query) echo 'Query updated. Rows affected: '.$this->db->affected_rows().'<br/>';

		$this->db->where('value', 'f');
		$query = $this->db->update('data_source_attributes', array('value'=>DB_FALSE));
		if($query) echo 'Query updated. Rows affected: '.$this->db->affected_rows().'<br/>';
	}

	public function migrate_roles_to_r131() {
		$cosi_db = $this->load->database('roles', TRUE);
		$query = $cosi_db->where('enabled', 't')->update('roles', array('enabled'=>DB_TRUE));
		if($query) echo 'Query updated. Rows affected: '.$cosi_db->affected_rows().'<br/>';
		$query = $cosi_db->where('enabled', 'f')->update('roles', array('enabled'=>DB_FALSE));
		if($query) echo 'Query updated. Rows affected: '.$cosi_db->affected_rows().'<br/>';

		$query = $cosi_db->get_where('roles', array('authentication_service_id'=>'AUTHENTICATION_SHIBBOLETH', 'shared_token'=>null));

		if($query->num_rows() > 0){
			foreach($query->result() as $q){
				echo $q->name.' set shared_token to'.$q->role_id.'<br/>';
				$cosi_db->where('role_id', $q->role_id)->update('roles', array('shared_token'=>$q->role_id));
			}
		}
	}

	public function fix_trim_roles_type_id() {
		$cosi_db = $this->load->database('roles', TRUE);
		$query = $cosi_db->get('roles');
		foreach($query->result() as $q){
			// echo $q->role_id.' >'.$q->role_type_id.'<br/>';
			$cosi_db->where('role_id', $q->role_id)->update('roles', array('role_type_id'=>trim($q->role_type_id)));
		}
		echo 'Query updated. Rows affected:'.$cosi_db->affected_rows();
	}

	public function syncmenu(){
		acl_enforce('REGISTRY_STAFF');
		$data['title'] = 'ARMS SyncMenu';
		$data['scripts'] = array('sync_app');
		$data['js_lib'] = array('core', 'angular', 'dataTables');
		$this->load->view("syncmenu_index", $data);
	}

	public function harvester() {
		acl_enforce('REGISTRY_STAFF');
		$data['title'] = 'ARMS Harvester Management';
		$data['scripts'] = array('harvester_app');
		$data['js_lib'] = array('core', 'angular');
		$this->load->view("harvester_app", $data);
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
		
		$sampleRecordUrls = array('http://services.ands.org.au/documentation/rifcs/1.6/examples/eg-collection-1.xml',
			'http://services.ands.org.au/documentation/rifcs/1.6/examples/eg-party-1.xml',
			'http://services.ands.org.au/documentation/rifcs/1.6/examples/eg-service-1.xml',
			'http://services.ands.org.au/documentation/rifcs/1.6/examples/eg-activity-1.xml');

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

	function solr_search() {
		acl_enforce('REGISTRY_STAFF');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

		$data = file_get_contents("php://input");
		$data = json_decode($data, true);
		$query = $data['query'];

		$this->load->library('solr');
		$this->solr
			->setOpt('rows', '0')
			->setOpt('fl', 'id')
			->setOpt('q', $query);
		$result = $this->solr->executeSearch(true);
		echo json_encode($result);
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
		$totalEnrichTime = 0; $totalIndexTime = 0; $allErrors = array();
		$solr_docs = array();
		$results = array();
		foreach($keys as $key){
			$result = array();
			$ro = $this->ro->getPublishedByKey($key);
			$error = array();
			if($ro){
				$this->benchmark->mark('enrich_ro_start');
				//enrich
				if($task=='sync' || $task=='enrich'){
					try{
                        $ro->processIdentifiers();
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

				if($task == 'fast_sync' || $task == 'fast_enrich') {
					try{
						$ro->updateExtRif();
					} catch (Exception $e){
						array_push($error, $e->getMessage());
					}
				}

				if($task=='sync' || $task=='enrich'){
					try{
						$ro->enrich();
					}catch(Exception $e){
						array_push($error, $e->getMessage());
					}
				}


				$this->benchmark->mark('enrich_ro_end');

				//index
				
				if($task=='sync' || $task=='index' || $task=='fast_sync'){
					try{
						$solr_docs[] = $ro->indexable_json();
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

		if($task=='sync' || $task=='index' || $task=='fast_sync'){
			$this->solr->add_json(json_encode($solr_docs));
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

	function status() {
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$data = array();

		$data['solr'] = array(
			'url' => get_config_item('solr_url')
		);

		$data['deployment'] = array(
			'state' => get_config_item('deployment_state')
		);

		$data['admin'] = array(
			'name' => get_config_item('site_admin'),
			'email' => get_config_item('site_admin_email')
		);

		echo json_encode($data);
	}

	function test(){
		set_exception_handler('json_exception_handler');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$this->load->model('data_source/data_sources', 'ds');
		$this->load->model('registry_object/registry_objects', 'ro');

		// $ro = $this->ro->getByID(189615);

		// echo json_encode($ro->indexable_json());

		// $ro->sync();

		// $ros = array(425663,425664,425665,425666,425667,425668,425669,425670,425671,425672,425673,425674,425675,425676,425677,425878,425880,425881,425882,425883,425884,425885,425886,425887,425888,425889,425890,425891,425892,425893,425894,425895,425896,425897,425898,425899,425900,425901,425902,425903,425904,425905,425906,425907,425908,425909,425910,425911,425912,425913,425914,425915,425916,425917,425918,425919,425920,425922,425923,425924,425925,425926,425927,425778,425779,425780,425781,425782,425783,425784,42578);

		// $this->benchmark->mark('start');
		// foreach($ros as $id) {
		// 	$ro = $this->ro->getByID($id);
		// 	if ($ro) {

		// 		ulog('investigating '.$id);
		// 		$this->benchmark->mark('code_start');
		// 		$size = sizeof($ro->findMatchingRecords());
		// 		$this->benchmark->mark('code_end');
		// 		ulog('found '.$size.' matching records after '. $this->benchmark->elapsed_time('code_start', 'code_end'));
		// 	} else {
		// 		ulog('no ro for '.$id);
		// 		echo 'No RO for '.$id.'<br/>';
		// 	}
		// }
		// $this->benchmark->mark('end');
		// ulog('finished. Took '.$this->benchmark->elapsed_time('start', 'end'));
		
		// $ro = $this->ro->getByID(425888);
		// $this->benchmark->mark('code_start');
		// $matching = $ro->findMatchingRecords(array(), array(), array(), true);
		// $this->benchmark->mark('code_end');
		// echo 'took '.$this->benchmark->elapsed_time('code_start', 'code_end');
		// var_dump($matching);

		// $this->load->model('registry_object/indexers/solr_indexer', 'indexer');
		// $this->indexer->set_ro($ro);
		// $payload = $this->indexer->construct_payload();
		// echo json_encode($payload);
		// 
		// $ids = $this->ro->getIDsByDataSourceID(130, false, 'PUBLISHED');
		// foreach($ids as $id) {
		// 	$ro = $this->ro->getByID($id);
		// 	echo json_encode($ro->indexable_json());
		// 	unset($ro);
		// }

		$ro = $this->ro->getByID(15144);
		echo json_encode($ro->indexable_json());
	}

	function fixRelationships($id) {
		$this->load->model('registry_object/registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$relationships = $ro->getAllRelatedObjects(false, true, true);
		$already_sync = array();
		foreach($relationships as $r){
			if(!in_array($r['registry_object_id'], $already_sync)){
				$rr = $this->ro->getByID($r['registry_object_id']);
				$rr->sync();
				$already_sync[] = $rr->id;
				echo $rr->id. ' > '. $rr->class. ' > '.$rr->title.'<br/>';
			}
		}
		echo 'done';
	}

	function flush_buffers(){
		ob_flush();
		flush();
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
				if($msg = $ro->sync(true,99999999)!=true){
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