<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Core Tags Model for Registry Object
 *
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 *
 */

class Registry_object_tags extends CI_Model {

	/**
	 * get a list of tags based on a set of given keys
	 * @param  [array] $keys [list of registry object keys to get the tags from]
	 * @return [array]       [array of resulting tags]
	 */
	public function getTagsByKeys($keys){
		$this->load->model('registry_object/registry_objects', 'ro');
		$tags = array();
		foreach($keys as $key){
			$ro = $this->ro->getPublishedByKey($key);
			$ro_tags = $ro->getTags();
			foreach($ro_tags as $tag){
				if(!in_array($tag, $tags)) array_push($tags, $tag);
			}
			unset($ro);
		}
		return $tags;
	}

	/**
	 * index keys after adding a single tag, this function performs the add tag functionality as well
	 * @param  [array] $keys [list of registry object keys to add tags and index]
	 * @param  [string] $tag  [a tag to add, must not be null or empty string]
	 * @return [void]
	 */
	public function batchIndexAddTag($keys, $tag, $tag_type){
		$this->load->model('registry_object/registry_objects', 'ro');
		$_CI =& get_instance();
		$solrXML = '';
		$chunkSize = 400; 
		$arraySize = sizeof($keys);
		for($i = 0 ; $i < $arraySize ; $i++){
			$key = $keys[$i];
			$ro = $this->ro->getPublishedByKey($key);
			if($ro){
				$ro->addTag($tag, $tag_type);
				$ro->sync();
			}
			unset($ro);
		}
	}

}