<?php
/**
 * Page controller
 * This controller main purpose is to display static pages
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Theme_page extends MX_Controller {

	/**
	 * Index / Home page
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return view 
	 */
	function index(){
		$theme_pages = json_decode($this->getThemePageIndex(),true);
		$this->blade
			->set('theme_pages', $theme_pages)
			->render('theme_page/index');
	}

	function view($theme) {
		$theme = json_decode($this->fetch_theme_page_by_slug($theme), true);
		$this->blade
			->set('lib', array('colorbox', 'mustache'))
			->set('scripts', array('theme_page'))
			->set('theme', $theme)
			->render('theme_page/view');
	}

	function get($theme) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		echo $this->fetch_theme_page_by_slug($theme);
	}

	function test(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		echo $this->getThemePageIndex();
	}

	/**
	 * INTERNAL. Useful function helper for getting a theme page data from registry_endpoint
	 * @param  string $slug
	 * @return content
	 */
	public function fetch_theme_page_by_slug($slug){
		$url = registry_url().'services/rda/getThemePage/' . $slug;
		$contents = @file_get_contents($url);
		return $contents;
	}

	/**
	 * INTERNAL. Useful function helper for returning the theme pages index
	 * @return content
	 */
	public function getThemePageIndex(){
		$url = registry_url().'services/rda/getThemePageIndex/';
		$contents = @file_get_contents($url);
		return $contents;
	}

	public function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->blade->set_template('omega');
	}
}