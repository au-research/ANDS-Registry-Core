<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class XML_Extension extends ExtensionBase
{
	
	private $_xml;	// internal pointer for RIFCS XML
	private $_rif;	
	private $_simplexml;
	
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	

	/*
	 * Record data methods
	 */
	 
	function getXML($record_data_id = NULL)
	{
		if (!is_null($this->_xml) && (is_null($record_data_id) || $this->_xml->record_data_id == $record_data_id))
		{
			return $this->_xml->xml;
		}
		else
		{
			$this->_xml = new _xml($this->ro->id, $record_data_id);
			return $this->_xml->xml;
		}
	}
	
	function getSimpleXML($record_data_id = NULL)
	{
		if (!is_null($this->_simplexml) && (is_null($record_data_id) || (!is_null($this->_xml) && ($this->_xml->record_data_id == $record_data_id))))
		{
			return $this->_simplexml;
		}
		else
		{

            $xml = $this->getRif($record_data_id);

			if (!$xml) {
                throw new Exception("No XML found for record: ". $this->ro->id);
            }

			$this->_simplexml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOENT);

			$namespaces = $this->_simplexml->getNamespaces(true);
			if ( !in_array(RIFCS_NAMESPACE, $namespaces) )
			{    
				$this->_simplexml->addAttribute("xmlns",RIFCS_NAMESPACE);
			}

			$this->_simplexml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
			return $this->_simplexml;
		}
	}
	
		 
	function updateXML($data, $current = TRUE, $scheme = NULL)
	{
		$_xml = new _xml($this->ro->id);
		$changed = $_xml->update($data, $current, $scheme);

		if (is_null($scheme))
		{
			$this->_xml = $_xml;
			if (is_null($scheme)) {
				$this->_rif =& $_xml;
			}

			$this->_simplexml = simplexml_load_string($_xml->xml);
			$this->_simplexml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		}
        return $changed;
	}
	
	
	function getXMLVersions()
	{
		$versions = array();
		$result = $this->db->select('id, timestamp, scheme, current')->get_where('record_data', array('registry_object_id'=>$this->ro->id));
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() AS $row)
			{
				$versions[] = $row;
			}
		}
		$result->free_result();
		return $versions;
	}


	function getRif($revision_id = null){

		if ($revision_id)
		{
			$result = $this->db->select('data')->get_where('record_data', array('id'=>$revision_id, 'scheme'=>RIFCS_SCHEME));
		}
		else
		{
			$result = $this->db->select('data')->order_by('timestamp','desc')->limit(1)->get_where('record_data', array('registry_object_id'=>$this->ro->id, 'scheme'=>RIFCS_SCHEME));
		}

		$data = false;
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() AS $row)
			{
				$data = $row['data'];
			}
		}
		$result->free_result();
		return $data;
	}

    function getRecordDataInScheme($revision_id = null, $scheme = RIFCS_SCHEME){

        if ($revision_id)
        {
            $result = $this->db->select('data')->get_where('record_data', array('id'=>$revision_id, 'scheme'=>$scheme));
        }
        else
        {
            $result = $this->db->select('data')->order_by('timestamp','desc')->limit(1)->get_where('record_data', array('registry_object_id'=>$this->ro->id, 'scheme'=>$scheme));
        }

        $data = false;
        if ($result->num_rows() > 0)
        {
            foreach($result->result_array() AS $row)
            {
                $data = $row['data'];
            }
        }
        $result->free_result();
        return $data;
    }


	function getNativeFormat($record_data_id = NULL)
	{
		$data = null;
		$result = $this->db->select('scheme')->order_by('timestamp','desc')->limit(1)->get_where('record_data','registry_object_id = ' . $this->ro->id . ' AND scheme !="'. RIFCS_SCHEME .'"');
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() AS $row)
			{
				$data = $row['scheme'];
			}
		}
		else
		{
			$data = 'rif';
		}
		$result->free_result();
		return $data;
	}

	function getNativeFormatData($record_data_id = NULL)
	{
		$data = null;
		$result = $this->db->select('data')->order_by('timestamp','desc')->limit(1)->get_where('record_data', 'registry_object_id = ' . $this->ro->id . ' AND scheme !="'. RIFCS_SCHEME .'"');
		if ($result->num_rows() > 0)
		{
			foreach($result->result_array() AS $row)
			{
				$data = $row['data'];
			}
		}
		else
		{
			$data = $this->getRif();
		}
		$result->free_result();
		return $data;
	}
}



class _xml
{
	const DEFAULT_SCHEME = RIFCS_SCHEME;
	
	public $registry_object_id;
	public $record_data_id;
	public $_CI;
	public $db;
	public $xml;
	public $current;
	public $timestamp;
	public $scheme = RIFCS_SCHEME;
	
	function __construct($registry_object_id = NULL, $record_data_id = NULL)
	{
		if (!is_numeric($registry_object_id) && !is_null($registry_object_id)) 
		{
			throw new Exception("Registry Object _xml class must be initialised with a numeric Identifier");
		}
		
		$this->registry_object_id = $registry_object_id;	// Set this object's ID
		$this->record_data_id = $record_data_id;
		$this->_CI =& get_instance();						// Get a pointer to the framework's instance
		$this->db =& $this->_CI->db;						// Shorthand pointer to database
		
		if (!is_null($registry_object_id))
		{
			return $this->init($record_data_id);
		}
		return $this;
	}	
	
	
	function init($record_data_id)
	{
		if (is_null($record_data_id))
		{
			$query = $this->db->order_by('timestamp','DESC')->get_where('record_data', array('registry_object_id' => $this->registry_object_id), 1);
		}
		else 
		{
			$query = $this->db->order_by('timestamp','DESC')->get_where('record_data', array('id' => $record_data_id), 1);
		}
	
		if ($query->num_rows() == 1)
		{
			//$result = array_pop($query->result_array());
			$results = $query->result_array();
			$result = $results[0];
			$query->free_result();
			$this->xml = $result['data'];
			$this->timestamp = $result['timestamp'];
			$this->scheme = $result['scheme'];	
			$this->record_data_id = $result['id'];
		}
		return $this;
	}
	
	function update($xml, $current = TRUE, $scheme = NULL)
	{
		if (is_null($scheme)) { $scheme = self::DEFAULT_SCHEME; }
		
		$this->xml = $xml;
		$newHash = md5($xml);
		$oldHash = '';
		$this->current = $current;
		$this->scheme = $scheme;
		if($current == TRUE)
		{

			$query = $this->db->select('*')->from('record_data')->where(array('registry_object_id' => $this->registry_object_id, 'scheme'=>$scheme))->order_by('id DESC')->limit(1)->get();
			if ($query->num_rows() > 0)
			{
				$results = $query->result_array();
				$result = $results[0];
				$query->free_result();
				$oldHash = $result['hash'];
			}
			
			if($oldHash != $newHash){
				$this->db->insert('record_data', array(
													'registry_object_id'=>$this->registry_object_id,
													'data' => $xml,
													'timestamp' => time(),
													'current' => ($current ? "TRUE" : "FALSE"),
													'scheme' => $scheme,
													'hash' => $newHash
												));
			}
			else
			{
				$this->db->where('id', $result['id'])->update('record_data',array('current'=>"TRUE"));
			}
		}
		else
		{
				$this->db->insert('record_data', array(
												'registry_object_id'=>$this->registry_object_id,
												'data' => $xml,
												'timestamp' => time(),
												'current' => ($current ? "TRUE" : "FALSE"),
												'scheme' => $scheme,
												'hash' => $newHash
											));

		}
        return ($oldHash != $newHash);
	}
}
	
	