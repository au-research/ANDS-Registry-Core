<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Revisions_extension extends ExtensionBase
{
	
	
	/**
	 * @ignore
	 * This MUST be defined in order to get the in-scope extensions variables
	 */
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}
	
	function getAllRevisions()
	{
		//date_default_timezone_set('GMT');
		$this->db->where(array('registry_object_id' => $this->ro->id, 'scheme'=>RIFCS_SCHEME));
		$this->db->order_by('timestamp', 'desc');
		$this->db->select('*')->from('record_data');
		$result = $this->db->get();	
		$revisions = array();
		foreach($result->result_array() AS $r)
		{
			$time = date("F j, Y, g:i a", $r['timestamp']);
			if($r['current'] == TRUE) $r['current'] = ' (current version)';
			else $r['current'] = '';
			$revisions[$time] = $r;
		}
		$result->free_result();
		return $revisions;
	}


	function getRevision($revision_id)
	{
		//date_default_timezone_set('GMT');
		$this->db->where(array('registry_object_id' => $this->ro->id, 'id'=>$revision_id, 'scheme'=>RIFCS_SCHEME));
		$this->db->select('*')->from('record_data');
		$revision = $this->db->get();	
		return $revision->result_array();
	}
}