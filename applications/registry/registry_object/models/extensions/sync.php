<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sync_extension extends ExtensionBase{
		
	function __construct($ro_pointer){
		parent::__construct($ro_pointer);
	}

	/**
	 * Do an enrich and commit
	 * With great power comes great responsibility
	 * @return boolean/string [if it's a string, it's an error message]
	 */
	function sync(){
		try {
			$this->_CI->load->library('solr');
			$this->ro->enrich();
			if($this->ro->status=='PUBLISHED'){
				$solrXML = $this->ro->transformForSOLR();
				$this->_CI->solr->addDoc("<add>".$solrXML."</add>");
				$this->_CI->solr->commit();
			}
		} catch (Exception $e) {
			return $e;
		}
		return true;
	}
}
	
	