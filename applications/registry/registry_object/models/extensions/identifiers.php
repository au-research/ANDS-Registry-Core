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
	
	