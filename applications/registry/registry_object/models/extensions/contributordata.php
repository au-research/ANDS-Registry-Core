<?php

class ContributorData_Extension extends ExtensionBase
{
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}		
	
	/* This should be a loader for classes in a seperate directory called "suggestors" 

		Workflow should be:

			- check if there is a file with the name of the suggestor in our suggestors directory
			- instantiate that class and pass it the reference to this registry object
			- have the logic for each suggestor in it's own file and class to avoid clutter
			- the suggester's ->suggest() method returns an array of suggested links (not sure what format this object should be in?)

	*/
	function getContributorData()
	{

		$contributorData['contributor'] = $this->ro->getAttribute('group');
		$this->_CI->load->library('solr');
		$this->_CI->solr->setOpt('q', '*:*');
		$this->_CI->solr->setOpt('fq', 'group:("'.$this->ro->getAttribute('group').'")');
		$this->_CI->solr->setOpt('fq', '-id:("'.$this->ro->id.'")');		
		$this->_CI->solr->setFacetOpt('field', 'class');
		$this->_CI->solr->setFacetOpt('field', 'subject_value_resolved');		
		$this->_CI->solr->setFacetOpt('mincount','1');

		$result = $this->_CI->solr->executeSearch();

		//we want to select counts by class of  registry objects which have the same group;
		$classes = $this->_CI->solr->getFacetResult('class');

		foreach($classes as $class=>$num){
			$contributorData['contents'][$class] = $num;
		}


		//we want to select all subjects of records which have this as a contibuting group;

		$subjects = $this->_CI->solr->getFacetResult('subject_value_resolved');

		foreach($subjects as $subject=>$num){
			$contributorData['subjects'][$subject] = $num;
		}

		//we want to select all objects of type group which have this as a contibuting group;

		$this->_CI->solr->setOpt('rows', '3000');
		$this->_CI->solr->setOpt('fq', 'type:("group")');
		$groups = $this->_CI->solr->executeSearch();
		// var_dump($groups->{'response'}->{'numFound'});
		// var_dump($groups->{'response'}->{'docs'});
		foreach($groups->{'response'}->{'docs'} as $group){
			if($group->{'slug'}!=$this->ro->getAttribute('slug'))
			$contributorData['groups'][$group->{'list_title'}] = $group->{'slug'};
		}

		// clear the solr options to set up new query to get the latest 5 collections

		$this->_CI->solr->clearOpt('fq');
		$this->_CI->solr->setOpt('rows','5');
		$this->_CI->solr->setOpt('fq', 'group:("'.$this->ro->getAttribute('group').'")');		
		$this->_CI->solr->setOpt('fq', 'class:("collection")');
		$this->_CI->solr->setOpt('sort', 'update_timestamp desc');		
		$collectionsAdded = $this->_CI->solr->executeSearch();

		foreach($collectionsAdded->{'response'}->{'docs'} as $collection){
			// $contributorData['collections'][$collection->{'list_title'}] = $collection->{'slug'};
			$contributorData['collections'][] = array(
				'title' => $collection->{'list_title'},
				'slug' => $collection->{'slug'},
				'id' => $collection->{'id'}
			);
		}

		return $contributorData;

	}


}