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

	private function doTask($task){
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
		} catch (Exception $e) {
			throw new Exception ($e->getMessage());
		}
		
	}


}