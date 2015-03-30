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

	function list_task() {
		$result['pending'] = array();
		$result['running'] = array();

		$query = $this->db->where('status', 'PENDING')->get('tasks');
		if ($query->num_rows() > 0) {
			$result['pending'] = $query->result_array();
		}

		$query = $this->db->where('status', 'RUNNING')->get('tasks');
		if ($query->num_rows() > 0) {
			$result['running'] = $query->result_array();
		}

		$query = $this->db->where('status', 'COMPLETED')->get('tasks');
		if ($query->num_rows() > 0) {
			$result['completed'] = $query->num_rows();
		}

		return $result;
	}

	function find_task() {
		//if there's a task already running, don't do anything
		$query = $this->db->where('status', 'RUNNING')->get('tasks');
		if($query->num_rows() > 5) {
			return false;
		}

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

		return false;
	}

	function is_running() {
		$query = $this->db->where('status', 'RUNNING')->get('tasks');
		if($query->num_rows() > 0) {
			return true;
		}
	}

	function find_specific_task($id) {
		$query = $this->db->where('id', $id)->get('tasks');
		if($query->num_rows() > 0) {
			$result = $query->result_array();
			return $result[0];
		} else {
			return false;
		}
	}

	function clear_pending() {
		return $this->db->delete('tasks', array('status'=>'PENDING'));
	}
}