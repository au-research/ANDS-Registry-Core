<?php


class Quality_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
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