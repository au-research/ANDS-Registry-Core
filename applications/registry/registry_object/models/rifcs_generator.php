<?php


class Rifcs_generator extends CI_Model {
			
			
	function xml($data_source_key, $registry_object_key, $title, $group)
	{
		$rifcs_xml = '<registryObject group="'.$group.'">
    					<key>'.$registry_object_key.'</key>
    					<originatingSource>'.$data_source_key.'</originatingSource>
    					<party type="group">
           				 <name type="primary">
       						 <namePart>'.$title.'</namePart>
      						</name>
      					</party>
     					 </registryObject>';
		return $rifcs_xml;
	}	
			
	
	/**
	 * @ignore
	 */
	function __construct()
	{
		parent::__construct();
	}	
		
}
