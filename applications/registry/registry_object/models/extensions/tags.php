<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tags_Extension extends ExtensionBase{
		
	function __construct($ro_pointer){
		parent::__construct($ro_pointer);
	}		

	function getTags(){
		$tags = array();
		$results = $this->db->select('tag')->from('registry_object_tags')->where('key', $this->ro->key)->get()->result_array();
		foreach($results as $r){
			array_push($tags, $r['tag']);
		}
		return $tags;
	}

	function hasTag($tag){
		$tags = $this->db->select('*')->from('registry_object_tags')->where('key', $this->ro->key)->where('tag', $tag)->get()->result_array();
		if(sizeof($tags) > 0){
			return true;
		}else return false;
	}

	function markTag($mark){
		$this->_CI->load->model('registry_object/registry_objects', 'ro_model');
		$ros = $this->_CI->ro_model->getAllByKey($this->ro->key);
		foreach($ros as $ro){
			$ro->tag = $mark;
			$ro->save();
		}
	}

	function addTag($tag){
		if(!$this->ro->hasTag($tag)){
			$data = array(
				'key'=>$this->ro->key,
				'tag'=>$tag
			);
			$this->db->insert('registry_object_tags', $data);
			$this->markTag(1);
			return true;
		}else return 'Already has the tag: '+$tag;
	}

	function removeTag($tag){
		$this->db->delete('registry_object_tags', array('key'=>$this->ro->key, 'tag'=>$tag));
		if(sizeof($this->ro->getTags())==0) {
			$this->markTag(0);
		}
		return true;
	}
}
	
	