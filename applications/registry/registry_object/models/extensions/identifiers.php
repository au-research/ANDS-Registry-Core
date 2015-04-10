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

	function getIdentifiers() {
		$sxml = $this->ro->getSimpleXML();
		$identifiersArray = array();
		foreach($sxml->xpath('//ro:'.$this->ro->class.'/ro:identifier') AS $identifier) {
			if((string)$identifier != '') {
				$identifiersArray[] = array(
					'identifier' => (string) $identifier,
					'identifier_type' => (string) $identifier['type']
				);
			}
		}
		return $identifiersArray;
	}



	public function findMatchingRecords($matches = array(), $tested_ids = array(), $tested_identifiers = array(), $recursive=true)
	{
		//if($this->ro->class=='collection') return $matches;
		if(sizeof($tested_ids) === 0) // first call
		{
			$tested_ids[] = $this->ro->id;
			$query = $this->db->get_where('registry_object_identifiers', array('registry_object_id' => $this->ro->id, 'identifier_type !='=> 'local'));
			$sql = "SELECT ro.registry_object_id, ro.class FROM `registry_object_identifiers` roi RIGHT JOIN `registry_objects` ro ON ro.registry_object_id = roi.registry_object_id  AND ro.status = 'PUBLISHED' WHERE roi.registry_object_id != ".$this->ro->id ." AND ro.class = '".$this->ro->class."' AND (";
			$qArray = array();
			$or = '';
			if(sizeof($query->result_array()) > 0)
			{
				foreach ($query->result_array() AS $row)
				{
					$sql .= $or."(roi.identifier = ? AND roi.identifier_type = ?)"; 
					$or = ' OR ';
					$qArray[] = $row['identifier'];
					$qArray[] = $row['identifier_type'];

					$tested_identifiers[] = $row['identifier'];
				}
				$sql .= ")";
				$query = $this->db->query($sql, $qArray);
				if(sizeof($query->result_array()) > 0){
					foreach ($query->result_array() AS $ro){
						if(!in_array($ro['registry_object_id'], $matches)){
							$matches[] = $ro['registry_object_id'];
						}
					}
					if($recursive) // continue traversing is needed
						return $matches = $this->findMatchingRecords($matches, $tested_ids, $tested_identifiers);
				}
			}
		}
		elseif(sizeof($matches) > 0)
		{
			foreach ($matches AS $registry_object_id)
			{
				if(!in_array($registry_object_id, $tested_ids))
				{
					$tested_ids[] = $registry_object_id;
					// $query = $this->db->where('registry_object_identifiers', array('registry_object_id' => $registry_object_id, 'identifier_type !='=> 'local'))->where_not_in('identifier', $tested_identifiers)->get('');
					// $query = $this->db->where('registry_object_identifiers', array('registry_object_id' => $registry_object_id, 'identifier_type !='=> 'local'))->where_not_in('identifier', $tested_identifiers)->get('');

					$query = $this->db->select('*')
							->from('registry_object_identifiers')
							->where('registry_object_id', $registry_object_id)
							->where('identifier_type !=', 'local')
							->where_not_in('identifier', $tested_identifiers)
							->get();
					$sql = "SELECT ro.registry_object_id, ro.class FROM `registry_object_identifiers` roi RIGHT JOIN `registry_objects` ro ON ro.registry_object_id = roi.registry_object_id  AND ro.status = 'PUBLISHED' WHERE roi.registry_object_id != ".$registry_object_id." AND ro.class = '".$this->ro->class."' AND (";
					$qArray = array();
					$or = '';
					if(sizeof($query->result_array()) > 0)
					{
						foreach ($query->result_array() AS $row)
						{
							$sql .= $or."(roi.identifier = ? AND roi.identifier_type = ?)"; 
							$or = ' OR ';
							$qArray[] = $row['identifier'];
							$qArray[] = $row['identifier_type'];
							$tested_identifiers[] = $row['identifier'];
						}
						$sql .= ")";
						
						$query = $this->db->query($sql, $qArray);
						
						if(sizeof($query->result_array()) > 0){
							foreach ($query->result_array() AS $ro)
							{
								if(!in_array($ro['registry_object_id'], $tested_ids) && !in_array($ro['registry_object_id'], $matches)){
									$matches[] = $ro['registry_object_id'];
								}
							}
							return $matches = $this->findMatchingRecords($matches, $tested_ids, $tested_identifiers);
						}
					}
				}

			}

		}
		return $matches;
	}
	
}
	