<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Identifiers_Extension extends ExtensionBase
{
		
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
		
	function processIdentifiers()
	{
		$sxml = $this->ro->getSimpleXML();	
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		$this->db->where(array('registry_object_id' => $this->ro->id));
		$this->db->delete('registry_object_identifiers');	
		foreach($sxml->xpath('//ro:'.$this->ro->class.'/ro:identifier') AS $identifier)
        {
           if((string)$identifier != '')
           {
	            $this->db->insert('registry_object_identifiers', 
					array(
							"registry_object_id"=>$this->ro->id, 
							"identifier"=>(string)$identifier,
							"identifier_type"=>(string)$identifier['type']
					)
				);
        	}
        }
	}

	/**
	 * SOLR. Find registry objects that share the same identifier
	 * @return array registry_object_id
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	public function findMatchingRecords(){
		$sxml = $this->ro->getSimpleXML();	
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		$this->db->where(array('registry_object_id' => $this->ro->id));
		$this->db->delete('registry_object_identifiers');	
		$identifiers = array();
		foreach($sxml->xpath('//ro:'.$this->ro->class.'/ro:identifier') AS $identifier) {
			if((string)$identifier != '') {
				$identifiers[] = (string) $identifier;
			}
		}
		foreach($identifiers as $i){
			$solr_query = implode('") OR identifier_value:("', $identifiers);
		}
		$solr_query = '(identifier_value:("'.$solr_query.'")) AND -id:'.$this->ro->id ;

		$this->_CI->load->library('solr');
		$this->_CI->solr->setOpt('q', $solr_query);
		$this->_CI->solr->setOpt('fl', 'id');
		$result = $this->_CI->solr->executeSearch(true);
		
		$matching_records = array();
		if (isset($result['response']['numFound']) && $result['response']['numFound'] > 0){
			foreach($result['response']['docs'] as $d){
				$matching_records[] = $d['id'];
			}
		}
		return $matching_records;
		// echo $solr_query;
	}
	
}
	
	