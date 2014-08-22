<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Import controller for use with api access or Harvester
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Import extends MX_Controller {

	/**
	 * Returns the harvest for a given data source
	 * @param  data_source_id $id
	 * @return json
	 */
	public function get($id=false) {
		if(!$id) throw new Exception('Data Source ID must be provided');
		$this->load->model('data_source/data_sources', 'ds');
		$ds = $this->ds->getByID($id);
		if(!$ds) throw new Exception('Data Source Not Found');
		
		$harvest_status = $ds->getHarvestStatus();
		if(!$harvest_status) throw new Exception('No Harvest Status Found');
		
		echo json_encode(
			array(
				'status' => 'OK',
				'data_source' => array(
					'id' => $id,
					'title' => $ds->title
				),
				'harvest_status' => $harvest_status
			)
		);
	}

	/**
	 * Mainly putHarvestData
	 * @param  data_source_id $id     
	 * @param  string  $method
	 * @return json          result
	 */
	public function put($id=false, $method='path') {
		if(!$id) throw new Exception('Data Source ID must be provided');
		$this->load->model('data_source/data_sources', 'ds');
		$ds = $this->ds->getByID($id);
		if(!$ds) throw new Exception('Data Source Not Found');
		if(!$method) throw new Exception('Put harvest data method must be provided');

		if($this->input->get('status') && $this->input->get('status')=='STOPPED') {
			$error_log = $ds->getHarvestErrorLog();
			$ds->append_log('Harvester Stopped By Error'.NL.$error_log,'error');
			return;
		}

		//get POST data from php input, mainly for angularJS POST
		$data = file_get_contents("php://input");
		$data = json_decode($data, true);
		$data = $data['data'];

		//switchboard
		switch($method) {
			case 'path': 
				$this->import_via_path($id, $this->input->get('batch'));
				break;
			case 'post':
				throw new Exception('Method POST is not implemented');
				break;
			case 'url':
				$this->simple_import('url', $id, $data);
				break;
			case 'xml':
				$this->simple_import('xml', $id, $data);
				break;
			default:
				throw new Exception('Invalid method');
				break;
		}
	}

	/**
	 * Import into a data source via downloaded file
	 * @param  data_source_id $id    
	 * @param  string $batch batch_id of the current batch
	 * @return json        
	 */
	private function import_via_path($id, $batch) {
		if(!$batch) throw new Exception('Batch ID expected');
		$dir = get_config_item('harvested_contents_path');
		if(!$dir) throw new Exception('Harvested Contents Path not configured');

		//getting the harvest_id
		$batch_query = $this->db->get_where('harvests', array('data_source_id'=>$id));
		if($batch_query->num_rows() > 0) {
			$batch_array = $batch_query->result_array();
			$harvest_id = $batch_array[0]['harvest_id'];
			$path = $dir.$id.'/'.$batch;

			$this->load->library('importer');
			$this->load->model('data_source/data_sources', 'ds');

			$ds = $this->ds->getByID($id);
			if(!$ds) throw new Exception('Data Source not found');

			$ds->updateHarvestStatus($harvest_id, 'IMPORTING');

			$this->importer->setCrosswalk($ds->provider_type);
			$this->importer->setDatasource($ds);
			$this->importer->setHarvestID($batch);
			// $this->importer->maintainStatus(); //records which already exists are harvested into their same status
			
			// $ds->append_log(print_r($batch_array, true));

			$count_before = $ds->count_total;

			if(!is_dir($path)) {
				//is not directory, it's a file
				$path = $path.'.xml';
				if(is_file($path)) {
					$xml = file_get_contents($path);
					$this->importer->setFilePath($path);
					try {
						$this->importer->setXML($xml);
						$this->importer->commit(true);
					} catch (Exception $e) {
						$ds->append_log($e, 'error');
						$ds->cancelHarvestRequest();
						$ds->setHarvestMessage('Stopped By Error: '. $e->getMessage());
						throw new Exception($e);
						return;
					}

					if($this->importer->getErrors()!=''){
						$has_error_msg = 'with error(s)';
					} else $has_error_msg = '';
					
					$ds->append_log(
						'Harvest Completed '.$has_error_msg.NL.
						// print_r($batch_array, true).NL.
						$this->importer->getMessages().NL.
						$this->importer->getErrors().NL
					);

					try {
						$ds->updateHarvestStatus($harvest_id, 'COMPLETED');
						$ds->setNextHarvestRun($harvest_id);
					} catch (Exception $e) {
						$ds->append_log($e, 'error');
						$ds->cancelHarvestRequest();
						$ds->setHarvestMessage('Stopped By Error: '. $e->getMessage());
						throw new Exception ($e);
						return;
					}

				} else {
					$ds->cancelHarvestRequest();
					throw new Exception ("File not found: ". $path);
					return;
				}
			} else {
				//is a directory
				$directory = scandir($path);
				
				$this->importer->setPartialCommitOnly(TRUE);

				$files = array();
				$natives  = array();
				foreach($directory as $f){
					if(endsWith($f, '.xml')) {
						$files[] = $f;
					}
				}

				$start_time = time();
				$message = array(
					'message' => 'Start Importing',
					'progress' => array(
						'start' => $start_time,
						'end' => false,
						'current' => 0,
						'total' => sizeof($files)
					)
				);
				$ds->updateImporterMessage($message);

				foreach($files as $index=>$f) {
					$xml = file_get_contents($path.'/'.$f);
					$this->importer->setFilePath($f);

					$filename = basename($f, '.xml');
					if(is_file($path.'/'.$filename.'.tmp')) {
						$this->importer->setNativeFile($path.'/'.$filename.'.tmp');
					} else {
						$this->importer->setNativeFile(false);
					}

					try {
						$this->importer->setXML($xml);
						$this->importer->commit(false);
						$message = array(
							'message' => 'Importing',
							'progress' => array(
								'start' => $start_time,
								'end' => false,
								'current' => $index,
								'total' => sizeof($files)
							)
						);
						$ds->updateImporterMessage($message);
					} catch (Exception $e) {
						$ds->append_log($e, 'error');
						$ds->cancelHarvestRequest();
						$ds->setHarvestMessage('Stopped By Error: '. $e->getMessage());
						throw new Exception($e);
						return;
					}
				}

				try {
					$message = array(
						'message' => 'Finishing Import Task',
						'progress' => array(
							'start' => $start_time,
							'end' => time()
						)
					);
					$ds->updateImporterMessage($message);
					$msg = $this->importer->finishImportTasks();

					if($this->importer->getErrors()!=''){
						$has_error_msg = 'with error(s)';
					} else $has_error_msg = '';

					$ds->append_log(
						'Harvest Completed '.$has_error_msg.NL.
						// print_r($batch_array, true).NL.
						$this->importer->getMessages().NL.
						$this->importer->getErrors().NL
					);
				} catch (Exception $e) {
					$ds->append_log($e, 'error');
					$ds->cancelHarvestRequest();
					$ds->setHarvestMessage('Stopped By Error: '. $e->getMessage());
					throw new Exception($e);
					return;
				}

				try {
					$message = array(
						'message' => 'Import Completed',
						'progress' => array(
							'start' => $start_time,
							'end' => time()
						)
					);
					$ds->updateImporterMessage($message);
					$ds->updateHarvestStatus($harvest_id, 'COMPLETED');
					$ds->setNextHarvestRun($harvest_id);
				} catch (Exception $e) {
					$ds->append_log($e, 'error');
					$ds->cancelHarvestRequest();
					$ds->setHarvestMessage('Stopped By Error: '. $e->getMessage());
					throw new Exception ($e);
				}
			}

			$ds->updateStats();

			//check for refresh mode
			if($ds->advanced_harvest_mode=='REFRESH'){
				$this->load->model("registry_object/registry_objects", "ro");
				$oldRegistryObjectIDs = $this->ro->getRecordsInDataSourceFromOldHarvest($ds->id, $batch);
				$oldCount = sizeof($oldRegistryObjectIDs);
				$count_after = (int) $ds->count_total;
				$count_after_delete = $count_after - $oldCount;

				if(($count_after - $oldCount) > ($count_before * 0.8)) {
					try{
						if(is_array($oldRegistryObjectIDs)){
							$deleted_keys = $this->ro->deleteRegistryObjects($oldRegistryObjectIDs, false);
							$ds->append_log(
								'Refresh Mode detected'.NL.
								'Record count before harvest: '.$count_before.NL.
								'Record count if Refresh performed: '.$count_after_delete.NL.
								'Record(s) deleted: '. sizeof($deleted_keys['deleted_record_keys'])
							);
						}
					} catch(Exception $e) {
					    $ds->append_log("ERROR REMOVING RECORD FROM PREVIOUS HARVEST: ".NL.$e, HARVEST_INFO, "harvester", "HARVESTER_INFO");
					    throw new Exception($e);
					    return;
					}
				} else {
					$ds->append_log(
						'Refresh Mode Cancelled. Total record count would be reduced by more than 20%. All records will be retained.'.NL.
						'Record count before harvest: '.$count_before.NL.
						'Record count if Refresh performed: '.$count_after_delete.NL
					);
				}
			}

			if($ds->advanced_harvest_mode == 'INCREMENTAL') {
				date_default_timezone_set('UTC');
				$ds->setAttribute("last_harvest_run_date",date("Y-m-d\TH:i:s\Z", time()));
				date_default_timezone_set('Australia/Canberra');
			} else {
				$ds->setAttribute("last_harvest_run_date",'');
			}
	
			echo json_encode(
				array(
					'status' => 'OK',
					'message' => $this->importer->getMessages()
				)
			);

		} else {
			throw new Exception ('No Harvest Records were found');
		}
	}

	//test function
	public function analyze($id=false, $batch=false) {
		if(!$id) throw new Exception('Data source ID expected');
		if(!$batch) throw new Exception('Batch ID expected');
		$dir = get_config_item('harvested_contents_path');
		if(!$dir) throw new Exeption('Harvested Contents Path not configured');

		$result = array();
		$path = $dir.$id.'/'.$batch;

		if(is_dir($path)) {
			$result['is_dir'] = true;
		} else {
			$result['is_dir'] = false;
		}

		if($result['is_dir']) {
			$files = scandir($path);
			foreach($files as $f){
				if(endsWith($f, '.xml')) {
					$result['files'][] = str_replace('.xml', '', $f);
				}
			}
			if($result['files']){
				sort($result['files']);
				$result['num_files'] = sizeof($result['files']);
			}
		}
		echo json_encode($result);
	}

	//test function
	public function miniImport($id=false, $batch=false, $file=false) {
		if(!$id) throw new Exception('Data source ID expected');
		if(!$batch) throw new Exception('Batch ID expected');
		$dir = get_config_item('harvested_contents_path');
		if(!$dir) throw new Exeption('Harvested Contents Path not configured');

		$this->load->library('importer');
		$this->load->model('data_source/data_sources', 'ds');
		$ds = $this->ds->getByID($id);
		if(!$ds) throw new Exception('Data Source not found');

		$this->importer->setDatasource($ds);

		$path = $dir.$id.'/'.$batch.'/'.$file.'.xml';
		if(is_file($path)) {
			$xml = file_get_contents($path);
			try {
				$this->importer->setXML($xml);
				// $this->importer->maintainStatus(); //records which already exists are harvested into their same status
				$this->importer->commit();
			} catch (Exception $e) {
				$ds->append_log($e, 'error');
				throw new Exception($e);
				return;
			}

			echo json_encode(array(
				'status' => 'OK',
				'message' => 'File '. $file. ' Ingested Successfully for Data Source '. $ds->title
			));

		}else {
			throw new Exception('File: '. $path. ' Not Found');
		}
	}

	/**
	 * Straightforward import from url or XML
	 * @param  string $type url|xml
	 * @param  data_source_id $id   
	 * @param  POST_DATA $data from the switchboard
	 * @return json       
	 */
	private function simple_import($type, $id, $data) {

		$this->load->library('importer');
		$ds = $this->ds->getByID($id);

		if ($type == 'url') {
			$url = trim($data['url']);
			if(!$url) throw new Exception('URL must be provided');
			if (!preg_match("/^https?:\/\/.*/",$url)){
				throw new Exception('URL must be valid http:// or https:// resource. Please try again');
				return;	
			}

			try {
				$ds->append_log('Import from URL: '. $data['url'].' started at '. date( 'Y-m-d H:i:s', time()));
				$xml = @file_get_contents($url);
			} catch (Exception $e) {
				$ds->append_log('Import from URL ('.$data['url'].') failed'.NL.$e->getMessage(), 'error');
				throw new Exception($e->getMessage());
				return;
			}
		}

		if($type=='xml') $xml = $data['xml'];

		if($xml || $type=='xml') {

			if($type=='xml') {
				$ds->append_log('Import from Pasted XML started at '. date( 'Y-m-d H:i:s', time()));
			}

			//check the xml
			if (strlen($xml)==0){
				if($type=='xml') $ds->append_log('Import from Pasted XML failed: Pasted content is empty', 'error');
				throw new Exception('Unable to retrieve any content. Make sure the content is not empty');
				return;
			}

			// if(!isValidXML($xml)){
			// 	if($type=='xml') $ds->append_log('Import from Pasted XML failed: Pasted content is not valid XML', 'error');
			// 	throw new Exception('Import failed, Input is not valid XML');
			// 	return;
			// }

			$xml = stripXMLHeader($xml);


			if(strpos($xml, '<registryObjects') === FALSE) $xml = wrapRegistryObjects($xml);

			try {
				$this->importer->runBenchMark = true;
				$this->importer->setXML($xml);
				// $this->importer->maintainStatus(); //records which already exists are harvested into their same status
				$this->importer->setCrosswalk($ds->provider_type);
				$this->importer->setDatasource($ds);
				$this->importer->commit();
			} catch (Exception $e) {
				if($type=='xml') $ds->append_log('Import from Pasted XML failed '.$e->getMessage(), 'error');
				if($type=='url') $ds->append_log('Import from URL failed '.NL.'URL: '.$url.NL.$e->getMessage(), 'error');
				throw new Exception($e->getMessage());
				return;
			}

			$error_log = $this->importer->getErrors();
			if($error_log && $error_log!='') {
				if($type=='xml') $ds->append_log('Import from Pasted XML failed due to errors'.$error_log, 'error');
				if($type=='url') $ds->append_log('Import from URL failed due to errors '.NL.'URL: '.$url.NL.$error_log, 'error');
				throw new Exception($error_log);
			}
		} elseif($type=='url') {
			throw new Exception('URL not contain any XML');
		} else {
			throw new Exception('Bad XML');
		}

		$import_msg = '';
		if($type=='xml') $import_msg.='Import from Pasted XML Completed!'.NL;
		if($type=='url') $import_msg.='Import from URL Completed!'.NL.'URL:'.$url.NL;
		$import_msg.=trim($this->importer->getMessages());
		$ds->append_log($import_msg);

		//all goes well
		echo json_encode(
			array(
				'status' => 'OK',
				'message' => $import_msg
			)
		);
	}

	/**
	 * List all harvest, currently used for harvester maintenance screen
	 * @return json 
	 */
	public function list_harvests() {
		$this->load->model('data_source/data_sources', 'ds');
		$harvests = $this->db->get('harvests');

		$result = array();
		foreach($harvests->result_array() as $harvest) {
			$result[] = $harvest;
		}

		foreach($result as &$r){
			$ds = $this->ds->getByID($r['data_source_id']);
			if($ds){
				$r['data_source_title'] = $ds->title;
				$r['record_owner'] = $ds->record_owner;
			}
			unset($ds);
		}

		echo json_encode(
			array(
				'status' => 'OK',
				'harvests' => $result
			)
		);
	}

	public function list_files($id=false) {
		if(!$id) throw new Exception('Data Source ID required');
		$dir = get_config_item('harvested_contents_path');
		if(!$dir) throw new Exception('Harvested Contents Path not configured');


		if($this->input->get('path')){
			$path = $this->input->get('path');
			if(!is_file($path)) throw new Exception('Path not found');
			$content = @file_get_contents($path);
			echo json_encode(array(
				'status' => 'OK',
				'content' => $content
			));
			return;
		}

		$path = $dir.$id;
		if(!is_dir($path)) throw new Exception('Datasource does not have any harvested path');
		$batches = array();
		foreach(scandir($path) as $f){
			if($f!="." && $f!="..") $batches[] = $f;
		}
		if(sizeof($batches) == 0) throw new Exception('Data source does not have any batch harvested');

		$result = array();

		foreach($batches as $b) {
			$link = $path.'/'.$b;
			if(is_file($link)) $result[] = array('type'=>'file', 'link'=>$link, 'name'=>$b);
			if(is_dir($link)) {
				$files = array();
				foreach(scandir($link) as $file){
					if($file!="." && $file!="..") $files[] = array('type'=>'file', 'link'=>$link.'/'.$file, 'name'=>$file);
				}
				$result[] = array('type'=>'folder', 'link'=>$link, 'files'=>$files, 'name'=>$b);
			}
		}

		echo json_encode(array(
			'status' => 'OK',
			'content' => $result
		));
	}

	/**
	 * constructor
	 * define header return type
	 */
	function __construct() {
		parent::__construct();
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		set_error_handler('json_error_handler');
	}
}