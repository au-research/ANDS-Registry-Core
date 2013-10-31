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
			$this->ro->enrich();
			if($this->ro->status=='PUBLISHED'){
				$solrXML = $this->ro->transformForSOLR();
				$this->solr->addDoc("<add>".$solrXML."</add>");
				$this->solr->commit();
			}
		} catch (Exception $e) {
			return $e;
		}
		return true;
	}
}
	
	