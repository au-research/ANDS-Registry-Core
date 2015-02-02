<?php
/**
 * Group controller
 * This controller main purpose is to display contributor pages
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Group extends MX_Controller {

	function index() {
		$groups = $this->groups->getAll();
		$this->blade
			->set('groups', $groups)
			->render('group/group_index');
	}

	function test() {
		$name = 'Australian Ocean Data Network';
		$slug = url_title($name, '-', true);
		$group = $this->groups->get($slug);
		echo json_encode($group['custom_data']['overview']);
	}

	function view($slug) {
		$group = $this->groups->get($slug);
		$this->blade
			->set('group', $group)
			->render('group/group_view');
	}

	function get() {
		$group_name = $this->input->get('group') ? $this->input->get('group') : false;
		if($group_name) {
			$group = $this->groups->fetchData($group_name);
			if ($group) {
				$result = $group;
			} else {
				$result = array(
					'name' => $this->input->get('group'),
					'nodata' => true
				);
			}
		} else {
			$groups = $this->groups->getOwnedGroups();
			$result = array(
				'groups' => $groups
			);
		}
		echo json_encode($result);
	}

	function save() {
		$group_name = $this->input->get('group') ? $this->input->get('group') : false;
		$data = json_decode(file_get_contents("php://input"), true);
		$data = $data['data'];
		$result = $this->groups->saveData($group_name, $data);
		if ($result) {
			echo json_encode(array(
				'status' => 'success',
				'message' => 'The draft has been saved at '.gmdate("Y-m-d H:i:s", time())
			));
		} else {
			echo json_encode(array(
				'status' => 'error',
				'message' => 'Save failed!'
			));
		}
	}

	function cms() {
		acl_enforce('REGISTRY_STAFF', '', true);
		$this->blade
			->set('scripts', array('contributor_app', 'contributor_factory'))
			->set('lib', array('textAngular'))
			->render('group/group_cms');
	}

	function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->load->model('groups');
	}
}