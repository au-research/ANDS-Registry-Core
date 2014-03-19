<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sync_extension extends ExtensionBase{
		
	function __construct($ro_pointer){
		parent::__construct($ro_pointer);
	}

	/**
	 * Do an enrich and commit, if full is provided, do add relationships and update quality metadata as well
	 * With great power comes great responsibility
	 * @param boolean $full determine whether to do addRelationships() and update quality metadata
	 * @return boolean/string [if it's a string, it's an error message]
	 */
	function sync($full = true){
		try {
			$this->_CI->load->library('solr');
			if($full){
				$this->ro->processIdentifiers();
				$this->ro->addRelationships();
				$this->ro->update_quality_metadata();
			}
			$this->ro->enrich();
			if($this->ro->status=='PUBLISHED'){
				$solrXML = $this->ro->transformForSOLR();
				$this->_CI->solr->addDoc("<add>".$solrXML."</add>");
				$this->_CI->solr->commit();
			}
		} catch (Exception $e) {
			return 'error: '.$e;
		}
		return true;
	}
}