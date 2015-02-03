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
		$this->blade->render('home');
	}

	/**
	 * About page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function about() {
		$this->blade->render('about');
	}

	/**
	 * Privacy Policy
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function privacy() {
		$this->blade->render('privacy_policy');
	}

	/**
	 * Disclaimer page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function disclaimer() {
		$this->blade->render('disclaimer');
	}


    /**
     * Help page
     * @author Liz Woods <liz.woods@ands.org.au>
     * @return view
     */
    function help() {
        $this->blade->render('help');
    }



	/**
	 * Display the sitemap
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function sitemap() {}



	public function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->blade->set_template('omega');
	}
}