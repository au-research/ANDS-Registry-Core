<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Subject_Extension extends ExtensionBase
{

	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
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
	
	