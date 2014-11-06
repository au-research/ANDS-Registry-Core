<?php

/**
 * Generic Authenticator
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

class Task_mgr extends CI_Model {

	function __construct(){
		set_exception_handler('json_exception_handler');
	}

	function add_task($task) {
		if(!isset($task['priority'])) $task['priority'] = 1;
		$task['date_added'] = date('Y-m-d H:i:s',time());
		$task['status'] = 'PENDING';
		$this->db->insert('tasks', $task);
	}

	function find_task() {
		// 0 priority task to be executed immediately
		$query = $this->db->where('status', 'PENDING')->where('priority', '0')->get('tasks');
		if($query->num_rows() > 0) {
			//return first task
			$result = $query->result_array();
			return $result[0];
		}

		//get a list of pending task ordered by priority
		$query = $this->db->where('status', 'PENDING')->order_by('priority')->get('tasks');
		if($query->num_rows() > 0) {
			//return first task
			$result = $query->result_array();
			return $result[0];
		}
	}

}