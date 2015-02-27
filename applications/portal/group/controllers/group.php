<?php
/**
 * Group controller
 * This controller main purpose is to display contributor pages
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Group extends MX_Controller {

	function index() {
		if ($this->input->get('preview')) {
			$slug = url_title($this->input->get('preview'), '-', true);
			$group = $this->groups->get($slug, 'DRAFT');
			$this->blade
				->set('group', $group)
				->render('group/group_view');
		} else {
			$groups = $this->groups->getAll();
			$this->blade
				->set('contributors', $groups)
				->render('group/group_index');
		}
	}

	function view($slug) {
		$group = $this->groups->get($slug);
		$this->blade
			->set('group', $group)
			->set('title', $group['title'])
			->render('group/group_view');
	}

	function get() {
		$group_name = $this->input->get('group') ? $this->input->get('group') : false;
		if($group_name) {
			$group = $this->groups->fetchData($group_name, 'DRAFT');
			if(!$group) $group = $this->groups->fetchData($group_name, 'PUBLISHED');
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
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$group_name = $this->input->get('group') ? $this->input->get('group') : false;
		$data = json_decode(file_get_contents("php://input"), true);
		$data = $data['data'];
		$result = $this->groups->saveData($group_name, $data);
		if ($result) {
			$message = '';
			if($data['status']=='DRAFT') {
				$message = 'The draft has been saved at '.gmdate("Y-m-d H:i:s", time());
			} elseif ($data['status']=='REQUESTED') {
				$message = 'The draft has been saved and requested for approval at '.gmdate("Y-m-d H:i:s", time());
			} elseif ($data['status']=='PUBLISHED') {
				$message = 'The draft has been published at '.gmdate("Y-m-d H:i:s", time());
			}
			echo json_encode(array(
				'status' => 'success',
				'message' => $message
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