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

	function view($slug) {
		$group = $this->groups->get($slug);
		$this->blade
			->set('group', $group)
			->render('group/group_view');
	}

	function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->load->model('groups');
	}
}