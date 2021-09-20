<?php
$config['default_template'] = 'ands-green';
global $environment_directives;
if (isset($environment_directives) && isset($environment_directives['portal'])) {
	$config['default_model'] = $environment_directives['portal']['default_model'];
} else {
	$config['default_model'] = 'registry_object';
}


$config['subjects'] = array(
	array(
		'display'=>'Humanities and Social Sciences',
		'codes' => array('13','16','17','19','20','21','22'),
		'img' => 'Humanities_3.jpg'
	),
	array(
		'display' => 'Business, Economics and Law',
		'codes' => array('14','15','18'),
		'img' => 'Business_3.jpg'
	),
	array(
		'display' => 'Medical and Health Sciences',
		'slug' => 'medical-and-health-sciences',
		'codes' => array('11'),
		'img' => 'Medical_1.jpg'
	),
	array(
		'display' => 'Engineering, Computing and Technology',
		'codes' => array('08','09','10'),
		'img' => 'Engineering_1.jpg'
	),
    array(
        'display'=>'Built Environment and Design',
        'codes' => array('12'),
        'img' => 'BuiltEnvDesign_1.jpg'
    ),
	array(
		'display' => 'Biological Sciences',
		'codes' => array('06'),
		'img' => 'Biological_1.jpg'
	),
    array(
        'display'=>'Agricultural and Veterinary Sciences',
        'codes' => array('07'),
        'img' => 'AgriCultAndVet_1.jpg'
    ),
	array(
		'display' => 'Environmental Sciences',
		'codes' => array('05'),
		'img' => 'Environmental_1.jpg'
	),
	array(
		'display' => 'Earth Sciences',
		'codes' => array('04'),
		'img' => 'EarthSciences_2.jpg'
	),
	array(
		'display'=>'Physical, Chemical and Mathematical Sciences',
		'codes' => array('01','02','03'),
		'img' => 'Physical_1.jpg'
	)
);

$config['subjects_categories'] = array(
	'keywords' 
		=> array(
			'display' => 'Keywords',
			'list'=> array('anzlic-theme', 'australia', 'caab', 'external_territories', 'cultural_group', 'DEEDI eResearch Archive Subjects', 'ISO Keywords', 'keyword', 'Local', 'local', 'marlin_regions', 'marlin_subjects', 'ocean_and_sea_regions', 'person_org', 'states/territories', 'Subject Keywords')
			),
	'scot' 
		=> array(
			'display' => 'Schools of Online Thesaurus',
			'list' => array('scot')
			),
	'pont' 
		=> array(
			'display' => 'Powerhouse Museum Object Name Thesaurus',
			'list' => array('pmont', 'pont')
			),
		
	'psychit' 
		=> array(
			'display' => 'Thesaurus of psychological index terms',
			'list' => array('Psychit', 'psychit')
			),
	'anzsrc' 
		=> array(
			'display' => 'ANZSRC',
			'list' => array('ANZSRC', 'anzsrc', 'anzsrc-rfcd', 'anzsrc-for-2020','anzsrc-seo-2020', 'anzsrc-seo', 'anzsrc-toa')
			),
	'apt' 
		=> array(
			'display' => 'Australian Pictorial Thesaurus',
			'list' => array('apt')
			),
	'gcmd' 
		=> array(
			'display' => 'GCMD Keywords',
		'list' => array('gcmd')
			),
	'lcsh' 
		=> array(
			'display' => 'LCSH',
			'list' => array('lcsh')
			),
    'iso639-3'
    => array(
        'display' => 'iso639-3 Language',
        'list' => array('iso639-3')
    )
		
);