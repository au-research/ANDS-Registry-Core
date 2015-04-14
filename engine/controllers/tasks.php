<?php
class Tasks extends CI_Controller {

	public function index() {
		$data['title'] = 'ANDS Task Manager';
		$data['scripts'] = array('task_mgr');
		$data['js_lib'] = array('core', 'angular129');
		$this->load->view('tasks_manager', $data);
	}

	public function list_task(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$this->load->model('task_mgr');
		$list = $this->task_mgr->list_task();
		echo json_encode($list);
	}

	public function next_task() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$this->load->model('task_mgr');
		$task = $this->task_mgr->find_task();
		echo json_encode($task);
	}

	public function add_task() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$data = json_decode(file_get_contents("php://input"), true);
		// echo json_encode($data);
		$type = $data['task'];
		$params = $data['params'];
		// echo json_encode($data);

		$this->load->model('task_mgr');
		$task = array(
			'name' => $type,
			'params' => $params
		);
		$this->task_mgr->add_task($task);
	}

	public function clear_pending(){
		$this->load->model('task_mgr');
		$this->task_mgr->clear_pending();
	}

	public function test() {
		$task = array(
			'id' => 100,
			'name' => 'sync',
			'params' => 'type=solr_query&id=+class:party',
		);
		$this->doTask($task);
		// $this->task_mgr->add_task($task);
	}

	public function exe() {
		$this->load->model('task_mgr');
		$task = $this->task_mgr->find_task();
		if($task) {
			$this->doTask($task);
		} else {
			if($running = $this->task_mgr->is_running()) {
				echo json_encode(array('message'=>'A task is already running'));
			} else {
				echo json_encode(array('message'=>'No task to execute'));
			}
		}
	}

	public function doByID($id) {
		$this->load->model('task_mgr');
		$task = $this->task_mgr->find_specific_task($id);
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