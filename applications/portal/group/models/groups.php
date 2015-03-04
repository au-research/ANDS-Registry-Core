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
				'logo' 	=> $this->fetchLogo($key),
				'slug'	=> url_title($key, '-', true),
				'counts' => $value
			);
		}

		return $groups;
	}

	function fetchLogo($group) {
		$slug = url_title($group, '-', true);

		//check for custom logo that is published
		$data = $this->fetchData($slug);
		if ($data) {
			$data = json_decode($data->{'data'}, true);
			if (isset($data['logo'])) {
				return $data['logo'];
			}
		}

		//check for default path
		$path = 'applications/portal/group/assets/logos/'.$slug.'.jpg';
		$path2 = 'applications/portal/group/assets/logos/'.$slug.'.png';
		if (file_exists($path)) {
			return asset_url('group/logos/'.$slug.'.jpg', 'full_base_path');
		} elseif(file_exists($path2)) {
			return asset_url('group/logos/'.$slug.'.png', 'full_base_path');
		} else {
			 return false;
		}
		return false;
	}

	/**
	 * Get a list of funders
	 * funders are party of type group and has relation of isFundedBy
	 * @return [type] [description]
	 */
	function getFunders() {
		$this->load->library('solr');
		$this->solr
			->setFacetOpt('facet', 'true')
			->setOpt('fq', '+class:activity')
			->setFacetOpt('field', 'funders');
		$result = $this->solr->executeSearch();
		$result = $this->solr->getFacetResult('funders');
		
		$groups = array();
		foreach($result as $key=>$value) {
			$groups[] = array(
				'title' => $key,
				'logo' 	=> $this->fetchLogo($key),
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
	function get($slug, $prefer = 'PUBLISHED') {

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
			->setFacetOpt('limit', '-1')
			->setFacetOpt('sort', 'count')
			->executeSearch();

		$group['facet'] = array();

		//classes
		$group['facet']['class'] = array();
		$classes = $this->solr->getFacetResult('class');
		foreach ($classes as $class=>$num) {
			$group['facet']['class'][] = array(
				'name' => $class,
				'num'  => $num
			);
		}

		//subjects
		$group['facet']['subjects'] = array();
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
				'slug' => isset($doc['slug']) ? $doc['slug']:''
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

		//get custom fields from the database
		$data = $this->fetchData($group['title'], $prefer);
		if($data) {
			$group['has_custom_data'] = true;
			$group['custom_data'] = json_decode($data->{'data'}, true);
		} else {
			$group['has_custom_data'] = false;
		}

		return $group;
	}

	function fetchData($name='', $prefer='PUBLISHED') {
		$this->portal_db = $this->load->database('portal', TRUE);

		if($prefer=='PUBLISHED') {
			$result = $this->portal_db->get_where('contributor_pages', array('name'=>$name, 'status'=>'PUBLISHED'), 1, 0);
		} else {
			$result = $this->portal_db->get_where('contributor_pages', array('name'=>$name, 'status'=>'DRAFT'), 1, 0);
			if($result->num_rows() == 0) {
				$result = $this->portal_db->get_where('contributor_pages', array('name'=>$name, 'status'=>'REQUESTED'), 1, 0);
			} 
		}

		if ($result && $result->num_rows() > 0) {
			return $result->first_row();
		} else {
			return false;
		}
	}

	function getAllData() {
		$this->portal_db = $this->load->database('portal', TRUE);
		$result = $this->portal_db->select('name,status,date_modified')->get('contributor_pages');
		if ($result->num_rows() > 0) {
			return $result->result_array();
		}
	}

	function saveData($name='', $data=array()) {
		$this->portal_db = $this->load->database('portal', TRUE);

		$published = $this->fetchData($name, 'PUBLISHED');
		$drafts = $this->fetchData($name, 'DRAFT');

		if(!isset($data['status'])) $data['status'] = 'DRAFT';

		if(!$drafts) {
			//create a draft
			$data = array(
				'name' => $name,
				'authorative_datasource' => '0',
				'status' => $data['status'],
				'data' => json_encode($data['data']),
				'date_modified' => date("Y-m-d H:i:s"),
				'modified_who' => $this->user->localIdentifier()
			);
			$result = $this->portal_db->insert('contributor_pages', $data);
		} elseif ($drafts && $data['status']!='PUBLISHED') {
			//update the draft
			$data = array(
				'status' => $data['status'],
				'data' => json_encode($data['data']),
				'date_modified' => date("Y-m-d H:i:s"),
				'modified_who' => $this->user->localIdentifier()
			);
			$this->portal_db->where('name', $name);
			$this->portal_db->where_in('status', array('DRAFT', 'REQUESTED'));
			$result = $this->portal_db->update('contributor_pages', $data);
		} elseif ($data['status']=='PUBLISHED') {
			//destroy all
			$this->portal_db->where('name', $name)->delete('contributor_pages');
			//create a PUBLISHED
			$data = array(
				'name' => $name,
				'authorative_datasource' => '0',
				'status' => $data['status'],
				'data' => json_encode($data['data']),
				'date_modified' => date("Y-m-d H:i:s"),
				'modified_who' => $this->user->localIdentifier()
			);
			$result = $this->portal_db->insert('contributor_pages', $data);
		}
		
		return $result;
	}


	function getOwnedGroups() {
		if($this->user->hasFunction('REGISTRY_SUPERUSER')) {
			$groups = $this->getAll();
			$owned_groups = array();
			foreach($groups as $group) {
				array_push($owned_groups, $group['title']);
			}
		} else {
			$url = base_url().'registry/services/api/data_sources/';
			$owned_ds = $this->user->ownedDataSourceIDs();
			foreach($owned_ds as $ds) {
				$url.=$ds.'-';
			}
			$url.='/groups';

			$content = @file_get_contents($url);
			$content = json_decode($content, true);
			$owned_groups = array();
			if ($content['status']=='success') {
				foreach($owned_ds as $ds) {
					if(isset($content['message'][$ds])) {
						foreach($content['message'][$ds]['groups'] as $group) {
							if(!in_array($group, $owned_groups)) {
								array_push($owned_groups, $group);
							}
						}
					}
				}
			}
		}
		
		
		return $owned_groups;
	}

}