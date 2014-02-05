<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tags_Extension extends ExtensionBase{
		
	function __construct($ro_pointer){
		parent::__construct($ro_pointer);
	}		

	function getTags(){
		$tags = array();
		$results = $this->db->select('tag')->from('registry_object_tags')->where('key', $this->ro->key)->get()->result_array();
		if(sizeof($results)>0){
			foreach($results as $r){
				array_push($tags, $r['tag']);
			}
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

	function addTag($tag, $type='public', $user='', $user_from=''){
		if(!$this->ro->hasTag($tag) && trim($tag)!=''){

			if($user=='' && $user_from==''){
				$this->_CI->load->library('user');
				if($this->_CI->user->isLoggedIn()){
					$user = $this->_CI->user->name();
					$user_from = $this->_CI->user->authDomain();
				}
			}

			$data = array(
				'key'=>$this->ro->key,
				'tag'=>$tag,
				'date_created' => date('Y-m-d H:i:s',time()),
				'type'=>$type,
				'user'=>$user,
				'user_from'=>$user_from
			);
			
			$this->db->insert('registry_object_tags', $data);
			$this->markTag(1);
			return true;
		}else return 'Error Adding: '+$tag;
	}

	function removeTag($tag){
		$this->db->delete('registry_object_tags', array('key'=>$this->ro->key, 'tag'=>$tag));
		if(sizeof($this->ro->getTags())==0) {
			$this->markTag(0);
		}
		return true;
	}
}
	
	