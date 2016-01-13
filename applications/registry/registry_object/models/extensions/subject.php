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

    /**
     * Get the Portal Type for a Subject Type
     * @todo make portalConfigCategories a global thing that registry and portal can get
     * @param $type
     * @return int|string
     */
    public function getPortalTypes($type)
    {
        $portalConfigCategories = array(
            'keywords' => array(
                'display' => 'Keywords',
                'list' => array('anzlic-theme', 'australia', 'caab', 'external_territories', 'cultural_group', 'DEEDI eResearch Archive Subjects', 'ISO Keywords', 'iso639-3', 'keyword', 'Local', 'local', 'marlin_regions', 'marlin_subjects', 'ocean_and_sea_regions', 'person_org', 'states/territories', 'Subject Keywords')
            ),
            'scot' => array(
                'display' => 'Schools of Online Thesaurus',
                'list' => array('scot')
            ),
            'pont' => array(
                'display' => 'Powerhouse Museum Object Name Thesaurus',
                'list' => array('pmont', 'pont')
            ),

            'psychit' => array(
                'display' => 'Thesaurus of psychological index terms',
                'list' => array('Psychit', 'psychit')
            ),
            'anzsrc' => array(
                'display' => 'ANZSRC',
                'list' => array('ANZSRC', 'anzsrc', 'anzsrc-rfcd', 'anzsrc-seo', 'anzsrc-toa')
            ),
            'apt' => array(
                'display' => 'Australian Pictorial Thesaurus',
                'list' => array('apt')
            ),
            'gcmd' => array(
                'display' => 'GCMD Keywords',
                'list' => array('gcmd')
            ),
            'lcsh' => array(
                'display' => 'LCSH',
                'list' => array('lcsh')
            )
        );

        foreach ($portalConfigCategories as $key => $category) {
            foreach ($category['list'] as $list) {
                if (strtolower($list) == strtolower($type)) {
                    return $key;
                }
            }
        }

        return $type;

    }

}
	
	