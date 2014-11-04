<?php
class Tasks extends CI_Controller {

	public function index() {
		echo 'Hello World';
	}

	public function test() {
		$task = array(
			'name' 		=> 'sync',
			'params'	=> 'type=ds&id=303,192,4444'
		);
		$this->doTask($task);

		$task2 = array(
			'name' => 'sync',
			'params'	=> 'type=ro&id=475612,475616,12341234134'
		);
		// $this->doTask($task2);
	}

	public function add() {
		$this->load->model('task_mgr');

		$task = array(
			'name' 		=> 'sync',
			'params'	=> 'type=ro&id=475612,475616,12341234134',
			'priority' 	=> 0
		);

		$task2 = array(
			'name' 		=> 'sync',
			'params'	=> 'type=ds&id=303'
		);

		$task3 = array(
			'name' 		=> 'sync',
			'params'	=> 'type=ds&id=192'
		);

		// $this->task_mgr->add_task($task);
		$this->task_mgr->add_task($task2);
		$this->task_mgr->add_task($task3);
	}

	public function exe() {
		$this->load->model('task_mgr');
		$task = $this->task_mgr->find_task();
		$this->doTask($task);
	}

	private function doTask($task){
		$this->load->model('task_mgr');
		set_exception_handler('json_exception_handler');

		//parse
		$task_dict = explode('/', $task['name']);

		$task_class = $task['name'];
		
		if (!file_exists('engine/models/tasks/'.$task_class.'.php')) {
			throw new Exception('Task '.$task_class.' not found!');
		}

		try {
			$this->load->model('tasks/'.$task_class, 'task');
			$this->task->load_params($task);
			$this->task->run();
			$this->task->report();
			$this->task_mgr->complete_task($task);
		} catch (Exception $e) {
			throw new Exception ($e->getMessage());
		}
		
	}


}