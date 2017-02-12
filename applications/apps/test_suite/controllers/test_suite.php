<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Test Suite Index
 * Perform a series of test cases to determine the registry is fully functioning
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Test_suite extends MX_Controller {

	function index() {
		$data['title'] = 'Test Suite';
		$data['scripts'] = array('test_suite_app');
		$data['js_lib'] = array('core', 'angular', 'select2', 'location_capture_widget', 'googleapi', 'google_map');
		$this->load->view('test_suite_index', $data);
	}

	function tests() {
		$this->output->set_status_header(200);
		$this->output->set_header('Content-type: application/json');

		$tests = array();
		$dir_path = APP_PATH. 'test_suite/models/tests/';
		if (is_dir($dir_path)) {
			if ($dir_handler = opendir($dir_path)) {
				while (($file = readdir($dir_handler)) !== false) {
					if (filetype($dir_path . $file)=='file') {
						$tests[] = preg_replace('/\.php$/', '', $file);
					}
				}
				closedir($dir_handler);
			}
		}
		$this->msg($tests);
	}

	function do_test($test='') {
		$this->output->set_status_header(200);
		$this->output->set_header('Content-type: application/json');
		if($test=='') throw new Exception('Test must be specified');
		try{
			$this->load->model('tests/'.$test, 'test_mdl');
			$this->test_mdl->test();

			$result = array(
				'result' => $this->test_mdl->result,
				'report' => $this->test_mdl->report,
				'elapsed' => $this->test_mdl->elapsed,
				'memory_usage' => $this->test_mdl->memory_usage,
				'status' => $this->test_mdl->status
			);

			$this->msg($result);

		} catch (Exception $e) {
			throw new Exception($e);
		}
	}

	function msg($content) {
		echo json_encode(
			array(
				'status' => 'OK',
				'content' => $content
			)
		);
	}

	function __construct() {
		set_exception_handler('json_exception_handler');
	}
}
