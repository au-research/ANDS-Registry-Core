<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Import controller for use with api access or Harvester
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Import extends MX_Controller {

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

	public function put($id=false, $method='path') {
		if(!$id) throw new Exception('Data Source ID must be provided');
		$this->load->model('data_source/data_sources', 'ds');
		$ds = $this->ds->getByID($id);
		if(!$ds) throw new Exception('Data Source Not Found');
		if(!$method) throw new Exception('Put harvest data method must be provided');

		switch($method) {
			case 'path': 
				$this->import_via_path($id, $this->input->get('batch'));
				break;
			case 'post':
				var_dump($this->input->post());
				break;
			default:
				throw new Exception('Invalid method');
				break;
		}
	}

	private function import_via_path($id, $batch) {
		if(!$batch) throw new Exception('Batch ID expected');
		$dir = '/var/www/harvested_content/';

		// $this->load->helper('xml_helper');

		$batch_query = $this->db->get_where('harvests', array('data_source_id'=>$id));
		if($batch_query->num_rows() > 0) {
			$batch_array = $batch_query->result_array();
			$harvest_id = $batch_array[0]['harvest_id'];
			$path = $dir.$id.'/'.$batch;

			$this->load->library('importer');
			$this->load->model('data_source/data_sources', 'ds');

			$ds = $this->ds->getByID($id);
			if(!$ds) throw new Exception('Data Source not found');
			$this->importer->setCrosswalk($ds->provider_type);
			$this->importer->setDatasource($ds);
			
			
			if(!is_dir($path)) {
				//is not directory, it's a file
				$path = $path.'.xml';
				if(is_file($path)) {
					$xml = file_get_contents($path);
					try {
						$this->importer->setXML($xml);
						$this->importer->maintainStatus(); //records which already exists are harvested into their same status
						$this->importer->setCrosswalk($ds->provider_type);
						$this->importer->setDatasource($ds);
						$this->importer->commit();
					} catch (Exception $e) {
						throw new Exception($e);
						return;
					}

					try {
						$ds->updateHarvestStatus($harvest_id, 'COMPLETED');
						$ds->setNextHarvestRun($harvest_id);
					} catch (Exception $e) {
						$ds->append_log($e);
						throw new Exception ($e);
					}
					
					echo json_encode(
						array(
							'status' => 'OK',
							'message' => $this->importer->getMessages(),
							// 'error' => $this->importer->getErrors()
						)
					);

				} else {
					throw new Exception ("File not found: ". $path);
				}
			} else {
				//is a directory
				$files = scandir($path);
				foreach($files as $f){
					if(endsWith($f, '.xml')) {
						$xml = file_get_contents($path.'/'.$f);
						try {
							$this->importer->setXML($xml);
							$this->importer->maintainStatus(); //records which already exists are harvested into their same status
							$this->importer->commit();
						} catch (Exception $e) {
							throw new Exception($e);
							return;
						}
					}
				}
				try {
					$ds->updateHarvestStatus($harvest_id, 'COMPLETED');
					$ds->setNextHarvestRun($harvest_id);
				} catch (Exception $e) {
					$ds->append_log($e);
					throw new Exception ($e);
				}
				echo json_encode(
					array(
						'status' => 'OK',
						'message' => $this->importer->getMessages(),
						// 'error' => $this->importer->getErrors()
					)
				);
			}

		} else {
			throw new Exception ('No Harvest Records were found');
		}
	}

	public function list_harvests() {
		$this->load->model('data_source/data_sources', 'ds');
		$harvests = $this->db->get('harvests');

		$result = array();
		foreach($harvests->result_array() as $harvest) {
			$result[] = $harvest;
		}

		foreach($result as &$r){
			$ds = $this->ds->getByID($r['data_source_id']);
			$r['data_source_title'] = $ds->title;
			$r['record_owner'] = $ds->record_owner;
			unset($ds);
		}

		echo json_encode(
			array(
				'status' => 'OK',
				'harvests' => $result
			)
		);
	}

	function __construct() {
		parent::__construct();
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
	}
}