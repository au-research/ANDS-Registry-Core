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

		$highlevel = array(
			array(
				'display'=>'Humanities and Social Sciences',
				'codes' => array('13','16','17','19','20','21','22'),
				'img_src' => asset_url('images/subjects/Humanities_3.jpg', 'core')
			),
			array(
				'display' => 'Business, Economics and Law',
				'codes' => array('14','15','18'),
				'img_src' => asset_url('images/subjects/Business_1.jpg', 'core')
			),
			array(
				'display' => 'Medical and Health Sciences',
				'codes' => array('11','0707'),
				'img_src' => asset_url('images/subjects/Medical_1.jpg', 'core')
			),
			array(
				'display' => 'Engineering, Built Environment, Computing and Technology',
				'codes' => array('08','09','10','12'),
				'img_src' => asset_url('images/subjects/Engineering_1.jpg', 'core')
			),
			array(
				'display' => 'Biological Sciences',
				'codes' => array('0601','0603','0604','0605','0606','0607','0608','0699','0701','0702','0703','0704','0706','0799'),
				'img_src' => asset_url('images/subjects/Biological_1.jpg', 'core')
			),
			array(
				'display' => 'Environmental Sciences and Ecology',
				'codes' => array('05','0602'),
				'img_src' => asset_url('images/subjects/Environmental_1.jpg', 'core')
			),
			array(
				'display' => 'Earth Sciences',
				'codes' => array('04'),
				'img_src' => asset_url('images/subjects/EarthSciences_2.jpg', 'core')
			),
			array(
				'display'=>'Physical, Chemical and Mathematical Sciences',
				'codes' => array('01','02','03'),
				'img_src' => asset_url('images/subjects/Physical_1.jpg', 'core')
			)
		);

		foreach($highlevel as &$item) {
			$item['uri'] = base_url('search');
		}

		$this->record_hit('home');
		$this->blade
			->set('subjects', $toplevel['topConcepts'])
			->set('highlevel', $highlevel)
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