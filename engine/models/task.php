<?php

/**
 * Generic Authenticator
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

class Task extends CI_Model {

	public $messages = array(
		'log' => array()
	);
	public $id = false;

	function __construct(){
		set_exception_handler('json_exception_handler');
	}

	function init($id) {
		$this->id = $id;
	}

	public function log($log) {
		array_push($this->messages['log'], $log);
	}

	public function run() {
		$this->benchmark->mark('code_start');
		$this->hook_start();

		$stuff = array('status'=>'RUNNING');
		$this->update_db($stuff);

		//overwrite this method
		$this->run_task();

		$this->hook_end();
		$this->benchmark->mark('code_end');
		$this->elapsed = $this->benchmark->elapsed_time('code_start', 'code_end');
		$this->messages['elapsed'] = $this->elapsed;

		$stuff = array('status'=>'COMPLETED', 'message'=>json_encode($this->messages));
		$this->update_db($stuff);
	}

	public function update_db($stuff) {
		$this->db->where('id', $this->id);
		$this->db->update('tasks', $stuff);
	}

	//hooks for some particular reason
	public function hook_start() {}
	public function hook_end() {}

	//overwrite these methods
	public function load_params($task) {}
	public function run_task() {}
	public function report() {
		echo json_encode($this->messages);
	}


}