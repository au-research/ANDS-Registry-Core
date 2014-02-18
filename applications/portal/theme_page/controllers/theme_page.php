<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Theme_page extends MX_Controller {

	function index($slug=''){
		if($slug!=''){
			$this->view('slug');
		}else{
			$this->listing();
		}
	}

	function view($slug=''){
		$data['page'] = json_decode($this->fetch_theme_page_by_slug($slug), true);
		$data['title'] = $data['page']['title'];
		if(isset($data['page']['desc'])) $data['the_description'] = $data['page']['desc'];
		$data['scripts'] = array('portal_theme');
		$data['js_lib'] = array('angular', 'colorbox');
		$this->load->view('theme_page_index', $data);
	}

	function addTag(){
		$data['key'] = $this->input->post('key');
		$data['tag'] = $this->input->post('tag');
		$user_profile = oauth_getUser();
		$data['user'] = $user_profile['profile']->displayName;
		$data['user_from'] = $user_profile['service'];

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$this->config->item('registry_endpoint').'addTag');//post to SOLR
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);//execute the curl
		curl_close($ch);//close the curl

		$this->output->set_status_header(200);
		$this->output->set_header('Content-type: application/json');
		echo $content;
	}

	function syncRO(){
		$data['key'] = $this->input->post('key');

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$this->config->item('registry_endpoint').'syncRO');//post to SOLR
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);//execute the curl
		curl_close($ch);//close the curl

		$this->output->set_status_header(200);
		$this->output->set_header('Content-type: application/json');
		echo $content;
	}

	function suggestTag($lcsh=false){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$search = $this->input->get('q');

		$url = $this->config->item('registry_endpoint'). 'getTagSuggestion/'.$lcsh.'/?q='.$search;
		$contents = @file_get_contents($url);
		
		$terms = json_decode($contents);
		echo json_encode($terms);
	}

	function listing(){
		$data['index'] = json_decode($this->getThemePageIndex(), true);
		// var_dump($data);
		$data['scripts'] = array();
		$data['title'] = 'Theme Pages - Research Data Australia';
		$this->load->view('theme_page_listing', $data);
	}

	public function getThemePageBanner($slug){
		$data['page'] = json_decode($this->fetch_theme_page_by_slug($slug));
		$this->load->view('theme_page_banner', $data);
	}

	public function fetch_theme_page_by_slug($slug){
		$url = $this->config->item('registry_endpoint') . "getThemePage/" . $slug;
		$contents = @file_get_contents($url);
		return $contents;
	}

	public function getThemePageIndex(){
		$url = $this->config->item('registry_endpoint') . "getThemePageIndex/";
		$contents = @file_get_contents($url);
		return $contents;
	}
}