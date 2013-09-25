<?php

// The endpoint on the registry from which data is retrieved
$config['registry_endpoint'] =  base_url() . "/registry/services/rda/";

$config['topics_datafile'] =  asset_url("topics/topics.json", "shared");

// Prevent these logos from coming up in the connections box
$config['banned_images'] = array(
	'http://services.ands.org.au/documentation/logos/nhmrc_stacked_small.jpg'
);

$config['protocol'] = 'https';//((strpos(base_url(), "http://") === 0) ? "http" : "https");

//======================================
// SUBJECT CATEGORIES for FACET SEARCHING
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