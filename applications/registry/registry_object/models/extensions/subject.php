<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Subject_Extension extends ExtensionBase
{
		
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
		
	function processSubjects()
	{
		$subjectsResolved = array();
		$this->_CI->load->library('vocab');
		$sxml = $this->ro->getSimpleXML();		
		$sxml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
		$subjects = $sxml->xpath('//ro:subject');
		foreach ($subjects AS $subject)
		{
			$type = (string)$subject["type"];
			$value = (string)$subject;
			if(!array_key_exists($value, $subjectsResolved))
			{
				$resolvedValue = $this->_CI->vocab->resolveSubject($value, $type);
				$subjectsResolved[$value] = array('type'=>$type, 'value'=>$value, 'resolved'=>$resolvedValue['value'], 'uri'=>$resolvedValue['about']);
				if($resolvedValue['uriprefix'] != 'non-resolvable')
				{
					$broaderSubjects = $this->_CI->vocab->getBroaderSubjects($resolvedValue['uriprefix'],$value);
					foreach($broaderSubjects as $broaderSubject)
					{
						$subjectsResolved[$broaderSubject['notation']] = array('type'=>$type, 'value'=>$broaderSubject['notation'], 'resolved'=>$broaderSubject['value'], 'uri'=>$broaderSubject['about']);
					}
				}
			}
		}
		return $subjectsResolved;
	}
	
}
	
	