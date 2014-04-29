<?php


class Quality_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	/*
	 * 	Metadata operations
	 */
	function update_quality_metadata($runBenchMark = false)
	{
		$this->_CI->load->model('registry_object/quality_checker', 'qa');
		
		// Get and update our quality metadata 
	
		// use the optimised version of getRelatedClassesString (which does not use getConnections())
		$relatedClassStr = $this->ro->getRelatedClassesString(false);

		
		if($runBenchMark) $this->_CI->benchmark->mark('ro_qa_s1_end');
		
		$quality_metadata = $this->_CI->qa->get_quality_test_result($this->ro, $relatedClassStr);
		
		if($runBenchMark) $this->_CI->benchmark->mark('ro_qa_s2_end');
		
		$this->ro->error_count = substr_count($quality_metadata, 'class="error');
		$this->ro->warning_count = substr_count($quality_metadata, 'class="error');
		$this->ro->setMetadata('quality_html', $quality_metadata);
		// Get and update our quality LEVELs
		$quality_metadata = $this->_CI->qa->get_qa_level_test_result($this->ro, $relatedClassStr);
		if($runBenchMark) $this->_CI->benchmark->mark('ro_qa_s3_end');
		// LEO'S BLACK MAGIC FOR DETERMINING THE MAXIMAL LEVEL
		$reportDoc = new DOMDocument();
		$reportDoc->loadXML($quality_metadata);
		$nXPath = new DOMXpath($reportDoc);
		$errorElement = $nXPath->evaluate("//span[@class = 'qa_error']");
		$level = 4;
		for( $j=0; $j < $errorElement->length; $j++ )
		{
			if($errorElement->item($j)->getAttribute("level") < $level)
			{
				$level = $errorElement->item($j)->getAttribute("level");
				//print "error found".$level."\n";
			}
		}
		
		$level = $level-1;
		// GOLD STANDARD SHOULD BE 4!!!
		if($this->ro->gold_status_flag == 't')
		{
			$this->ro->quality_level = 4;
		}
		else
		{
			$this->ro->quality_level = $level;
		}
		$this->ro->setMetadata('level_html',$quality_metadata);
		
		$this->ro->save();
	}
	
	function set_metadata($name, $value = '')
	{
		$query = $this->db->get_where("registry_object_metadata", array('registry_object_id' => $this->id, 'attribute' => $name));
		if ($query->num_rows() == 1)
		{
			$this->db->where(array('registry_object_id'=>$this->id, 'attribute'=>$name));
			$this->db->update('registry_object_metadata', array('value'=>$value));
		}
		else
		{
			$this->db->insert('registry_object_metadata', array('registry_object_id'=>$this->id, 'attribute'=>$name, 'value'=>$value));
		}
	}

	function get_quality_text(){
		$query = $this->db->get_where("registry_object_metadata", array('registry_object_id' => $this->id, 'attribute' => 'level_html'));
		if($query->num_rows()==1){
			$result = $query->result();
			return $result[0]->value;
		}else return false;
	}

	function get_validation_text(){
		$query = $this->db->get_where("registry_object_metadata", array('registry_object_id' => $this->id, 'attribute' => 'quality_html'));
		if($query->num_rows()==1){
			$result = $query->result();
			return $result[0]->value;
		}else return false;
	}
}