<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Spotlight extends MX_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->config('spotlight');
		acl_enforce('PORTAL_STAFF');
	}

	function index(){
		// $this->checkIsWriteable();

		$data['js_lib'] = array('core', 'tinymce');
		$data['scripts'] = array('spotlight');
		$data['title'] = 'Spotlight CMS';
		$data['less'] = array('spotlight');

		$data['file'] = $this->read();
		// $data['file'] = '{"items":[{"id":"1","title":"Sample Spotlight Data","url":"http:\/\/sample.org.au\/site\/","url_text":"","img_url":"http:\/\/www.auscope.org.au\/_lib\/img\/lnav_logo.gif","img_attr":"","new_window":"yes","content":"<p>This is a sample organisation.<\/p>","visible":"yes"}]}';
		$data['items'] = $data['file']['items'];

		$this->load->view('spotlight_cms', $data);
	}

	function add(){
		$file = $this->read();
		$items = $file['items'];
		$new_file = array('items'=>array());

		$largest_id = 0;
		foreach($items as $i){
			$new_file['items'][] = $this->getID($i['id'], $items);
			if($i['id']>$largest_id) $largest_id = $i['id'];
		}

		$obj = array(
			'id'=>$largest_id+1,
			'title'=>$this->input->post('title'),
			'url'=>$this->input->post('url'),
			'url_text'=>$this->input->post('url_text'),
			'img_url'=>$this->input->post('img_url'),
			'img_attr'=>$this->input->post('img_attr'),
			'new_window'=>$this->input->post('new_window'),
			'content'=>$this->input->post('content'),
			'visible'=>$this->input->post('visible')
		);
		$new_file['items'][] = $obj;
		$this->write(json_encode($new_file));
	}

	function save($id){
		//var_dump($this->input->post());
		$obj = array(
			'id'=>$id,
			'title'=>$this->input->post('title'),
			'url'=>$this->input->post('url'),
			'url_text'=>$this->input->post('url_text'),
			'img_url'=>$this->input->post('img_url'),
			'img_attr'=>$this->input->post('img_attr'),
			'new_window'=>$this->input->post('new_window'),
			'content'=>$this->input->post('content'),
			'visible'=>$this->input->post('visible')
		);
		$file = $this->read();
		$items = $file['items'];
		$new_file = array('items'=>array());
		foreach($items as $i){
			if($i['id']==$id){
				$new_file['items'][] = $obj;
			}else{
				$new_file['items'][] = $this->getID($i['id'], $items);
			}
		}
		$this->write(json_encode($new_file));
	}

	function saveOrder(){
		$new_order = $this->input->post('data');
		$file = $this->read();
		$items = $file['items'];

		$new_file = array('items'=>array());
		foreach($new_order as $o){
			if($item = $this->getID($o, $items)) $new_file['items'][] = $item;
		}
		$this->write(json_encode($new_file));
	}

	function delete($id){
		$file = $this->read();
		$items = $file['items'];
		$items = $file['items'];
		$new_file = array('items'=>array());
		foreach($items as $i){
			if($i['id']!=$id){
				$new_file['items'][] = $this->getID($i['id'], $items);
			}
		}
		//var_dump($new_file);
		$this->write(json_encode($new_file));
	}

	private function checkIsWriteable()
	{
		if (!is_writable($this->config->item('spotlight_data_file')))
		{
			throw new Exception ("Spotlight Data File is not writeable - check file permission in: " . $this->config->item('spotlight_data_file'));
		}

		if(!file_exists($this->config->item('spotlight_data_file'))){
			throw new Exception ("Spotlight Data File is not found - check file exists in: ". $this->config->item('spotlight_data_file'));
		}
	}

	private function write($data){
		$this->load->helper('file');
		if(write_file($this->config->item('spotlight_data_file'), $data, 'w')){
			echo 'success';
		}else{
			echo 'Unable to write to file. Check File Permission';
		}		
	}

	private function read(){
		$this->load->helper('file');
		$file = read_file($this->config->item('spotlight_data_file'));
		return json_decode($file,true);
	}

	private function getID($id, $items){
		foreach($items as $i){
			if($i['id']==$id) return $i;
		}
		return false;
	}
}
	