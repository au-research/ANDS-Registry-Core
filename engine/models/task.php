<?php

/**
 * Generic Authenticator
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

class Task extends CI_Model {

	public $messages = array(
		'log' => array()
	);

	function __construct(){
		set_exception_handler('json_exception_handler');
	}

	public function log($log) {
		array_push($this->messages['log'], $log);
	}

	public function run() {
		$this->benchmark->mark('code_start');
		$this->run_task();
		$this->benchmark->mark('code_end');
		$this->elapsed = $this->benchmark->elapsed_time('code_start', 'code_end');
		$this->messages['elapsed'] = $this->elapsed;
	}

	public function load_params($params = false) {}
	public function run_task() {}
	public function report() {
		echo json_encode($this->messages);
	}


}