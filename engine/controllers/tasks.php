<?php
class Tasks extends CI_Controller {

	public function index() {
		$data['title'] = 'ANDS Task Manager';
		$data['scripts'] = array('task_mgr');
		$data['js_lib'] = array('core', 'angular129');
		$this->load->view('tasks_manager', $data);
	}

	public function test() {
		$task = array(
			'id' => 100,
			'name' => 'sync',
			'params' => 'type=ds&id=58,101',
		);
		$this->doTask($task);
		// $this->task_mgr->add_task($task);
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

		$this->task_mgr->add_task($task);
		$this->task_mgr->add_task($task);
		// $this->task_mgr->add_task($task2);
		// $this->task_mgr->add_task($task3);
	}

	public function exe() {
		$this->load->model('task_mgr');
		$task = $this->task_mgr->find_task();
		if($task) {
			$this->doTask($task);
		} else {
			echo json_encode(array('message'=>'No task to execute'));
		}
	}

	public function immediate($task_id) {
		$this->load->model('task_mgr');
		$task = $this->task_mgr->find_specific_task($task_id);
		if($task) {
			$this->doTask($task);
		} else {
			echo json_encode(array('message'=>'No task to execute'));
		}
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
			$this->task->init($task['id']);
			$this->task->load_params($task);
			$this->task->run();
			$this->task->report();
		} catch (Exception $e) {
			throw new Exception ($e->getMessage());
		}
		
	}


}