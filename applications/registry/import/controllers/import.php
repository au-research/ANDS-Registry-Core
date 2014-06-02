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
				$this->import_via_path($id);
				break;
			case 'post':
				var_dump($this->input->post());
				break;
			default:
				throw new Exception('Invalid method');
				break;
		}
	}

	private function import_via_path($id) {
		$dir = '/var/www/harvested_content/'.$id;
		$files = scandir($dir.'/45');
		var_dump($files);
	}


	function __construct() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
	}
}