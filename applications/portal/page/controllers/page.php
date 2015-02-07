<?php
/**
 * Page controller
 * This controller main purpose is to display static pages
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Page extends MX_Controller {

	/**
	 * Index / Home page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function index(){
		header('Content-Type: text/html; charset=utf-8');
		$this->load->library('vocab');
		$toplevel = $this->vocab->getTopLevel('anzsrc-for', array());
		$this->record_hit('home');
		$this->blade
			->set('subjects', $toplevel['topConcepts'])
			->render('home');
	}

	function test(){
		$this->load->library('vocab');
		$toplevel = $this->vocab->getTopLevel('anzsrc-for', array());
		var_dump($toplevel);
	}

	/**
	 * About page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function about() {
		$this->record_hit('about');
		$this->blade->render('about');
	}

	/**
	 * Privacy Policy
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function privacy() {
		$this->record_hit('privacy');
		$this->blade->render('privacy_policy');
	}

	/**
	 * Disclaimer page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function disclaimer() {
		$this->record_hit('disclaimer');
		$this->blade->render('disclaimer');
	}


    /**
     * Help page
     * @author Liz Woods <liz.woods@ands.org.au>
     * @return view
     */
    function help() {
    	$this->record_hit('help');
        $this->blade->render('help');
    }



	/**
	 * Display the sitemap
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function sitemap() {}

	function record_hit($page = 'home') {
		$event = array(
			'event'=>'portal_page',
			'page' => $page,
			'ip' => $this->input->ip_address(),
			'user_agent' => $this->input->user_agent()
		);
		ulog_terms($event,'portal');
	}


	public function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->blade->set_template('omega');
	}
}