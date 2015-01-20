<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Group Model
 * @todo explanation
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Groups extends CI_Model {

	/**
	 * get All groups from SOLR
	 * requires all registry objects with distinct groups to be indexed correctly before returning anything
	 * @return array 
	 */
	function getAll() {

		$this->load->library('solr');
		$this->solr
			->setFacetOpt('facet', 'true')
			->setOpt('fq', '+class:collection')
			->setFacetOpt('field', 'group');
		$result = $this->solr->executeSearch();
		$result = $this->solr->getFacetResult('group');

		$groups = array();
		foreach($result as $key=>$value) {
			$groups[] = array(
				'title' => $key,
				'logo' 	=> false,
				'slug'	=> url_title($key, '-', true),
				'counts' => $value
			);
		}

		return $groups;
	}

	/**
	 * return a single group based on the slug it provides
	 * the group returns will be an associative array
	 * @return group 
	 */
	function get($slug) {

		//find the group in SOLR
		//@todo could be shorten by caching the result?
		$group = array();
		$groups = $this->getAll();
		foreach($groups as $gr) {
			if($gr['slug']==$slug) {
				$group = $gr; break;
			}
		}

		//return empty if we can't find it
		if (empty($group)) return $group;

		//reload solr
		$this->load->library('solr');
		$this->solr->init();

		$this->solr->setOpt('fq', '+group:("'.$group['title'].'")');

		//facets
		$this->solr
			->setFacetOpt('field','class')
			->setFacetOpt('field', 'subject_value_resolved')
			->setFacetOpt('mincount', '1')
			->executeSearch();

		$group['facet'] = array();

		//classes
		$classes = $this->solr->getFacetResult('class');
		foreach ($classes as $class=>$num) {
			$group['facet']['class'][] = array(
				'name' => $class,
				'num'  => $num
			);
		}

		//subjects
		$subjects = $this->solr->getFacetResult('subject_value_resolved');
		foreach ($subjects as $subject=>$num) {
			$group['facet']['subjects'][] = array(
				'name' => $subject,
				'num'  => $num
			);
		}

		//reload and collect groups
		$group['groups'] = array();
		$result = $this->solr
			->setOpt('rows', '10')
			->setOpt('fl', 'id,slug,title')
			->executeSearch(true);
		foreach($result['response']['docs'] as $doc) {
			$group['groups'][] = array(
				'id' => $doc['id'],
				'title' => $doc['title'],
				'slug' => $doc['slug']
			);
		}

		//latest 5 collections
		$group['latest_collections'] = array();
		$this->solr
			->clearOpt('fq')
			->setOpt('rows', '5')
			->setOpt('fq', '+group:("'.$group['title'].'")')
			->setOpt('fq', '+class:collection')
			->setOpt('sort', 'update_timestamp desc');
		$result = $this->solr->executeSearch(true);
		foreach($result['response']['docs'] as $doc) {
			$group['latest_collections'][] = array(
				'id' => $doc['id'],
				'title' => $doc['title'],
				'slug' => $doc['slug']
			);
		}



		return $group;
	}

}