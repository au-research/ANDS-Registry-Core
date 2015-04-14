<?php
$config['default_template'] = 'omega';
$config['default_model'] = 'registry_object';

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
		'codes' => array('11','0707'),
		'img' => 'Medical_1.jpg'
	),
	array(
		'display' => 'Engineering, Built Environment, Computing and Technology',
		'codes' => array('08','09','10','12'),
		'img' => 'Engineering_1.jpg'
	),
	array(
		'display' => 'Biological Sciences',
		'codes' => array('0601','0603','0604','0605','0606','0607','0608','0699','0701','0702','0703','0704','0706','0799'),
		'img' => 'Biological_1.jpg'
	),
	array(
		'display' => 'Environmental Sciences and Ecology',
		'codes' => array('05','0602'),
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
			'list'=> array('anzlic-theme', 'australia', 'caab', 'external_territories', 'cultural_group', 'DEEDI eResearch Archive Subjects', 'ISO Keywords', 'iso639-3', 'keyword', 'Local', 'local', 'marlin_regions', 'marlin_subjects', 'ocean_and_sea_regions', 'person_org', 'states/territories', 'Subject Keywords')
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
			'list' => array('ANZSRC', 'anzsrc', 'anzsrc-rfcd', 'anzsrc-seo', 'anzsrc-toa')
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
			)
		
);