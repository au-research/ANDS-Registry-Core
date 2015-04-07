<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(REGISTRY_APP_PATH . "services/interfaces/_GenericPortalEndpoint.php");
/**
 * RDA Endpoint (allows RDA to query the registry)
 *
 *
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/services/rda_endpoint
 *
 */

class Rda extends MX_Controller implements GenericPortalEndpoint 
{
	// Some internal defaults 
	const response_format = "application/json";
	const default_retrieval_scheme = "extrif";
	const default_retrieval_status = PUBLISHED;


	/**
	 * Fetch a registry object from the registry by "SLUG"	
	 *
	 * Responds with a JSON array containing the data of the record's extrif
	 * (or a JSON-formatted error response, if no matching data is available)
	 *
	 * @param $_GET[slug] "SLUG" of the registry object to retrieve
	 * @param $_GET[status] A specific status to select (default is PUBLISHED)
	 */
	public function getRegistryObject()
	{	
		$this->load->model('registry_object/Registry_objects', 'ro');

		// Some validation on input
		if (!$this->input->get('slug') && !$this->input->get('registry_object_id') && !$this->input->get('any')) { 
			throw new Exception("No valid URL SLUG or registry_object_id specified.");
		}

		// Lightweight registry object get (get the latest version of the extRif for this record)
		// See registry_object/models/registry_objects for description of this method syntax
		$record = $this->ro->_get(array(
									array('args' => array(	'slug'=>$this->input->get('slug'),
															'registry_object_id'=>$this->input->get('registry_object_id'),
															'status'=>$this->input->get('status')
														),
						     		  'fn' => function($db, $args) {
									       $db->distinct()
										       ->select("record_data.data, registry_objects.key, registry_objects.registry_object_id")
										       ->from("registry_objects")
										       ->join("record_data",
											      "record_data.registry_object_id = registry_objects.registry_object_id",
											      "inner")
										       ->where("record_data.scheme", Rda::default_retrieval_scheme)
										       ->where("record_data.current", "TRUE");

											if ($args['registry_object_id'])
											{
												$db->where("registry_objects.registry_object_id", $args['registry_object_id']);
											}

											if ($args['slug'])
											{
												 $db->where("slug", $args['slug'])
											 		->where("registry_objects.status",
												     		($args['status'] ? $args['status'] : Rda::default_retrieval_status));
											}

									       $db->order_by("record_data.timestamp", "desc");
									       return $db;
								       })),
							   	false, 	// return RO object
							   	1 		// limit
								);

		// We should only have one record returned
		if ($record && count($record) > 0)
		{
			// Contributor pages logic (constants in engine/config/)
			$this->load->model('data_source/data_sources', 'ds');
			//we have a reord, get the object to get the object's group
			$theObject = $this->ro->getByID($record[0]['registry_object_id']);
			//see if this group has a contributor page
			$contributor = $this->db->get_where('institutional_pages',array('group' => $theObject->getAttribute('group')));
			if ($contributor->num_rows() >0)
			{				
				//if there is a contributor page see if the key of the page is this one (to cater for when a draft and published contibutor page exists)
				$contributorRecord = array_pop($contributor->result_array());
				$theContributor = $this->ro->getByID($contributorRecord['registry_object_id']);
				if($theContributor && $theContributor->getAttribute('key')==$record[0]['key'])
				{
					$record[0]['template'] = CONTRIBUTOR_PAGE_TEMPLATE;
				}
				
			}

			$result = json_encode($record[0]);
			$result_decoded = json_decode($result,true);
			if(!$result_decoded['data']) {
				$result = array();
				$theObject->enrich();
				$result['data'] = $theObject->getExtRif();
				$result['registry_object_id'] = $theObject->id;
				$result['key'] = $theObject->key;
				echo json_encode($result);
			} else echo $result;

			return;
		} else if(count($record)==0){
			//NO EXTRIF, attempt to create one
			if($this->input->get('slug')){
				$ro = $this->ro->getBySlug($this->input->get('slug'));
			}elseif($this->input->get('registry_object_id')){
				$ro = $this->ro->getByID($this->input->get('registry_object_id'));
			}
			if (!$ro) throw new Exception('Registry Object not found');

			try{
				$ro->enrich();
			} catch (Exception $e){
				throw new Exception('ERROR: Registry Object cannot be enriched: '. $e->getMessage());
			}

			$result = array();
			$this->load->model('data_source/data_sources', 'ds');
			$contributor = $this->db->get_where('institutional_pages',array('group' => $ro->getAttribute('group')));
			if ($contributor->num_rows() >0) {				
				$contributorRecord = array_pop($contributor->result_array());
				$theContributor = $this->ro->getByID($contributorRecord['registry_object_id']);
				if($theContributor && $theContributor->getAttribute('key')==$ro->key){
					$result['template'] = CONTRIBUTOR_PAGE_TEMPLATE;
				}
			}
			$result['data'] = $ro->getExtRif();
			$result['registry_object_id'] = $ro->id;
			$result['key'] = $ro->key;
			echo json_encode($result);
		}
		else
		{

			if ($this->input->get('slug'))
			{

				// Check for redirects from old slugs
				$query = $this->db->query("SELECT * FROM url_mappings u JOIN registry_objects r ON r.registry_object_id = u.registry_object_id WHERE u.slug = ?", $this->input->get('slug'));
				
				if ($query->num_rows() > 0)
				{
					$orphan_slug = array_pop($query->result_array());
					if ($orphan_slug['slug'] == $this->input->get('slug'))
					{
						throw new Exception("Error: Unable to fetch extRif, despite active SLUG mapping.");
					}
					
					$contents = array('redirect_registry_object_slug' => $orphan_slug['slug']);
					echo json_encode($contents);
					return;
				}

				// Check for orphans! (SLUGS whose registry_object has been deleted)
				$query = $this->db->select('search_title')->get_where('url_mappings',
											array("slug"=> $this->input->get('slug'), "registry_object_id IS NULL" => null));
				
				if ($query->num_rows() > 0)
				{
					$orphan_slug = array_pop($query->result_array());
					$contents = array('previously_valid_title' => $orphan_slug['search_title']);
					echo json_encode($contents);
					return;
				}
			}
			$contents = array('message'=>'404');
			echo json_encode($contents);
			return;
			//throw new Exception("No data could be selected for the specified URL/ID");
		}
	}

	/**
	 * Resolve a Registry Object ID, Key or Slug
	 * If multiple registry objects found based on slug, returns data.multiple = true
	 * @return json_array
	 */
	public function resolveRegistryObject() {
		if(!$this->input->get('any')){
			throw new Exception('Nothing to resolve');
		}

		//setup
		$ro_content = array();
		$any = $this->input->get('any');
		$this->load->model('registry_object/registry_objects', 'ro');

		//check for registry objects with the same slug
		$slug_search = $this->db->get_where('registry_objects', array('slug'=>$any));
		if($slug_search->num_rows() == 1) {
			//there's only 1 record
			$result_array = $slug_search->result_array();
			if($result_array[0]['registry_object_id']) $any = $result_array[0]['registry_object_id'];
			$ro = $this->ro->getByID($any);
		} elseif($slug_search->num_rows() > 1) {
			//there's more than 1 record
			$ro = $slug_search->result_array();
		} else {
			//not a slug, maybe an id or a key
			$ro = $this->ro->getByID($any);
			if(!$ro) $ro = $this->ro->getBySlug($any);
			if(!$ro) $ro = $this->ro->getPublishedByKey($any);
		}

		//maybe an old slug, check in mapping table
		if(!$ro) {
			$url_mappings = $this->db->get_where('url_mappings', array('slug'=>$any));
			if($url_mappings->num_rows() > 0) {
				//found it, it's an old thing
				$result_array = $url_mappings->result_array();
				if($result_array[0]['registry_object_id']) $any = $result_array[0]['registry_object_id'];
				$ro = $this->ro->getByID($any);
			}
		}

		if($ro && !is_array($ro)) {
			$ro_content['id'] = $ro->id;
			$ro_content['key'] = $ro->key;
			$ro_content['slug'] = $ro->slug;
			$contents['data'] = $ro_content;
			echo json_encode($contents);
			return;
		} elseif (is_array($ro)) {
			$data = array(
				'multiple'=>true,
				'message' => 'Multiple Records Found: '. sizeof($ro)
			);
			$contents['data'] = $data;
			echo json_encode($contents);
			return;
		} else {
			$contents = array('message' => 'Registry Object not found. Trying to resolve '.$any);
			echo json_encode($contents);
			return;
		}
	}


	/**
	* Fetch a list of connections from the registry
	* 
	* XXX: TODO
	* XXX: Must have limit/offset (20 per "class" of connection)
	* XXX: must deal with draft records (so need to be able to specify a specific ID)
	*
	* @param string $_GET['slug'] The SLUG of the registry object to get connections for
	*/
	public function getConnections()
	{
		$connections = array();

		// Some validation on input
		if (!($this->input->get('slug') || $this->input->get('registry_object_id') || $this->input->get('registry_object_key')))
		{ 
			throw new Exception("Invalid URL SLUG, registry_object_id or registry_object_key specified.");
		}

		// Some filter variables
		$limit = ($this->input->get('limit') ? $this->input->get('limit') : 5);
		$offset = ($this->input->get('offset') ? $this->input->get('offset') : null);
		$type_filter = ($this->input->get('type_filter') ? $this->input->get('type_filter') : null);

		// Get the RO instance for this registry object so we can fetch its connections
		$this->load->model('registry_object/registry_objects', 'ro');
		if ($this->input->get('slug')){
			$registry_object = $this->ro->getBySlug($this->input->get('slug'));
			$published_only = TRUE;
		}
		elseif ($this->input->get('registry_object_id'))
		{
			$registry_object = $this->ro->getByID($this->input->get('registry_object_id'));
			$published_only = TRUE;
		}elseif ($this->input->get('registry_object_key')){
			$registry_object = $this->ro->getPublishedByKey(urldecode($this->input->get('registry_object_key')));
			$published_only = TRUE;
		}

		if (!$registry_object)
		{
			throw new Exception("Unable to fetch connections for this registry object.");
		}

		// Include inferred connections from duplicates
		//getConnections($published_only = true, $specific_type = null, $limit = 100, $offset = 0, $include_dupe_connections = false)
		$connections = $registry_object->getConnections($published_only,$type_filter,$limit,$offset, true);
//var_dump($connections);
		// Return this registry object's connections
		echo json_encode(array("connections"=>$connections, 'class'=>$registry_object->class, 'slug'=>$registry_object->slug));
	}

	public function getRelatedInfoByIrId()
	{
		header('Content-type: application/json');
		$result = array();
		if (!($this->input->get('id')))
		{ 
			$result['message'] = "Invalid URL 'id' not specified.";
		}
		else{
			$id = $this->input->get("id");
			$query = $this->db->get_where('registry_object_identifier_relationships', array('id'=>$id), 1);
			if ($query->num_rows() > 0){
				$result_array = $query->result_array();
				$result['data'] = $result_array;
				if ($result_array[0]['related_object_identifier_type'] == 'orcid'){
					$result['data'][0]['connections_preview_div'] .= $this->resolveOrcid($result_array[0]['related_object_identifier'], 'html');
				}
				echo json_encode($result);
			}
			else
			{
				$result['message'] = 'No record found';
				echo json_encode($result);
			}
		}
	}

	function resolveOrcid($orcid, $format = ''){
		$ch = curl_init();
		$headers = array('Accept: application/orcid+json');
		curl_setopt($ch, CURLOPT_URL, "http://pub.orcid.org/".$orcid); # URL to post to
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers ); # custom headers, see above
		$result = curl_exec( $ch ); # run!
		curl_close($ch);

		if($format=='json'){
			return $result;
		}else if($format=='php'){
			return json_decode($result, true);
		}else if($format=='html'){
			$html = '';
			$result = json_decode($result, true);

			$first_name = $result['orcid-profile']['orcid-bio']['personal-details']['given-names']['value'];
			$last_name = $result['orcid-profile']['orcid-bio']['personal-details']['family-name']['value'];
			$name = $first_name.' '.$last_name;
			$bio = "";
			if(isset($result['orcid-profile']['orcid-bio']['biography'])){
				$bio = $result['orcid-profile']['orcid-bio']['biography']['value'];
			}
			$html .='<h4><a href="http://orcid.org/'.$orcid.'">'.$name.'</a></h4>';
			//$html.='<p><img src="'.asset_url('img/orcid_tagline_small.png', 'base').'"/></p>';
			$html.= '<p>'.$bio.'</p>';
			$html.='<a href="http://orcid.org/'.$orcid.'">View profile in</a><a href="http://orcid.org/'.$orcid.'"><img style="border:none;width:50px;margin-top:-5px;margin-left:5px" src="'.asset_url('img/orcid_tagline_small.png', 'base').'"/></a>';
			return $html;
		}

	}

	/**
	 * Fetch a list of suggested links
	 *
	 * XXX: TODO
	 */
	public function getSuggestedLinks()
	{
		$links = array();

		// Check that we can actually support this mode of request (ands/ABS/datacite, etc)
		switch ($this->input->get('suggestor'))
		{
			case "ands_identifiers":
				$suggestor = "ands_identifiers";
			break;
			case "ands_duplicates":
				$suggestor = "ands_duplicates";
			break;
			case "ands_subjects":
				$suggestor = "ands_subjects";
			break;
			case "datacite":
				$suggestor = "datacite";
			break;
			default:
				throw new Exception("Variant of suggested links is not supported.");
		}

		// Some validation on the target registry object
		if (! $this->input->get('slug') && !$this->input->get('id'))
		{ 
			throw new Exception("Invalid URL SLUG or registry_object_id specified.");
		}

		// Get the RO instance for this registry object so we can fetch its suggested links
		$this->load->model('registry_object/registry_objects', 'ro');
		if ($this->input->get('slug'))
		{
			$registry_object = $this->ro->getBySlug($this->input->get('slug'));
		}
		elseif ($this->input->get('id'))
		{
			$registry_object = $this->ro->getByID($this->input->get('id'));
		}

		if (!$registry_object)
		{
			throw new Exception("Unable to fetch suggested links for this registry object.");
		}

		$links = $registry_object->getSuggestedLinks($suggestor,$this->input->get('start'),$this->input->get('rows'));

		echo json_encode(array("links"=>$links));
	}

	/**
	 * Fetch a list of registry contents by group
	 *
	 * XXX: TODO
	 */
	public function getContributorData()
	{
		$contents = array();

		// Get the RO instance for this registry object so we can fetch its contributor datat
		$this->load->model('registry_object/registry_objects', 'ro');
		
		if ($this->input->get('id')) {
			$registry_object = $this->ro->getByID($this->input->get('id'));
		} elseif ($this->input->get('slug')){
			$registry_object = $this->ro->getBySlug($this->input->get('slug'));
		}

		if (!$registry_object) {
			throw new Exception("Unable to fetch contributor data registry object.");
		}
		

		// XXX: TODO: LIMIT and offset (pass to getSuggestedLinks...)
		$this->load->library('solr');
		$contents = $registry_object->getContributorData();

		echo json_encode(array("contents"=>$contents));
	}

	public function getInstitutionals(){
		$result_inst = $this->db->select('group, registry_object_id')->from('institutional_pages')->get();
		$inst = array();
		foreach($result_inst->result() as $r){
			array_push($inst, $r->registry_object_id);
		}
		$result_things = $this->db->select('title, slug, registry_object_id')->from('registry_objects')->where('status', 'PUBLISHED')->where_in('registry_object_id', $inst)->get();

		$things = array();
		foreach($result_things->result() as $vv) {
			$things[$vv->registry_object_id] = array('slug'=>$vv->slug);
		}

		$fresult = array();
		foreach($result_inst->result() as $r) {
			if(isset($things[$r->registry_object_id])){
				array_push($fresult, array(
					'registry_object_id' => $r->registry_object_id,
					'title' => $r->group,
					'slug' => $things[$r->registry_object_id]['slug']
				));
			}
		}
		
		echo json_encode(array("contents"=>$fresult));
	}

    public function getCollectionCreators(){
        $this->load->model('registry_object/registry_objects', 'ro');
        $ro_id = $this->input->get('id');
        $ro = $this->ro->getByID($ro_id);
        $returnStr = '';
        if($ro){

            $relationshipTypeArray = ['hasPrincipalInvestigator','principalInvestigator','author','coInvestigator','isOwnedBy','hasCollector'];
            $classArray = ['party'];
            $connections = $ro->getRelatedObjectsByClassAndRelationshipType($classArray ,$relationshipTypeArray);

            foreach($connections AS &$link)
            {
                if ($link['status'] == PUBLISHED)
                {
                    $returnStr .= "&rft.creator=".$link['title'];
                }
            }
        }
        if($returnStr=='')
        {
            $returnStr .= '&rft.creator=Anonymous';
        }
        echo $returnStr;
    }

	/**
	 * Fetch canned text for contributor page
	 *
	 * XXX: TODO
	 */
	public function getContributorText()
	{
		$cannedText = array();

		// Get the RO instance for this registry object so we can fetch its contributor datat
		$this->load->model('registry_object/registry_objects', 'ro');
		
		if ($this->input->get('slug'))
		{
			$registry_object = $this->ro->getBySlug($this->input->get('slug'));
		}
		elseif ($this->input->get('id'))
		{
			$registry_object = $this->ro->getByID($this->input->get('id'));
		}

		if (!$registry_object)
		{
			throw new Exception("Unable to fetch suggested links for this registry object.");
		}
		

		// XXX: TODO: LIMIT and offset (pass to getSuggestedLinks...)
	
		$cannedText = $registry_object->getContributorText();

		echo json_encode(array("theText"=>$cannedText));
	}
	/**
	 * Return a list of Spotlight Partners along with their brief description and location (URL)
	 */
	public function getSpotlight(){
		$this->output->set_content_type(rda::response_format);
		$partners = array();

		$this->load->helper('file');
		$file = read_file('./assets/shared/spotlight/spotlight.json');
		$file = json_decode($file, true);
		if(is_array($file['items']) && count($file['items']) > 0)
		{
			foreach($file['items'] as $partner){
				if($partner['visible']=='yes'){
					$item = array(
						'title'=>$partner['title'],
						'description'=>$partner['content'],
						'img_url'=>$partner['img_url'],
						'url'=>$partner['url'],
						'visible'=>$partner['visible']
					);
					if(isset($partner['new_window']) && $partner['new_window']=='yes') $item['new_window']=$partner['new_window'];
					if(isset($partner['img_attr'])) $item['img_attr']=$partner['img_attr'];
					if (isset($partner['url_text']) && $partner['url_text'])
					{
						$item['url_text'] = $partner['url_text'];
					}
					$partners[] = $item;
				}
			}
			$partners = array_reverse($partners);
		// services_spotlight_partners_data_source
			$this->output->set_output(json_encode(array("items"=>$partners)));
		}
		else{
			$spotlight = @file_get_contents('http://services.ands.org.au/documentation/placeholder/spotlight.json');
			if($spotlight)
				$partners = $spotlight;
			$this->output->set_output($partners);
		}

	}



	/**
	 * Return an array of contributors to the registry
	 * (returns unique "group" names, with published collections)
	 *
	 * XXX: TODO: Merge this group list with a list of contributor pages?
	 */
	public function getWhoContributes()
	{
		$contributors = array(); 

		// Get an array of groups in the registry using SOLR facets
		$this->load->library('solr');
		$this->solr->setOpt('rows',0);
		$this->solr->setOpt('q',''); // unset the default query XXX: REMOVE (THIS IS JUST FOR TESTING WITH NO PUBLISHED RECORDS!)
		$this->solr->addQueryCondition('+class:"collection"');
		$this->solr->setOpt('fl','');
		$this->solr->setFacetOpt('field', 'group');
		$this->solr->setFacetOpt('limit', '200');
		$this->solr->setFacetOpt('mincount', '1'); // at least one published collection (else don't return it)
		$result = $this->solr->getFacetResult('group');

		foreach($result AS $title => $count)
		{
			$contributors[] = array(
				'title' => $title,
				'type' => 'group', // XXX: maybe have this "contributor_page" for contributors???
				'collection_count' => $count
			);
		}

		echo json_encode(array("contributors"=>$contributors));
	}

	/**
	 * Get a JSON list of recently updated records grouped by subject area code
	 */
	public function getLatestActivityBySubject($period = '7', $vocabulary = 'anzsrc-for')
	{
		if (!is_numeric($period)) return false;
		$this->load->library('solr');
		$this->load->library('vocab');
		$this->solr->init();

		$this->solr->setFacetOpt('field','subject_value_resolved');
		$this->solr->setFacetOpt('mincount','1');
		$this->solr->setOpt('fq','record_created_timestamp:[NOW-'.$period.'DAY TO NOW]');
		$this->solr->executeSearch();

		$activity_counts = array();
		foreach($this->solr->getFacetResult('subject_value_resolved') AS $subject_label => $count)
		{
			$resolved_term = $this->vocab->resolveLabel($subject_label, $vocabulary);
			if ($resolved_term)
			{
				$resolved_term['num_records'] = $count;
				$activity_counts[] = $resolved_term;
			}
		}

		echo json_encode(
			array(
				"status" => "success",
				"info" => "Records updated in the registry for the last $period DAYS",
				"results" => $activity_counts
			)
		);

	}


	/**
	 * Return a tree/hierarchical structure of ancestors and descendent records of 
	 * a specified record (inferred from the isPartOf/partOf relationships).
	 *
	 * If the registry object ID is specified, draft connections will be included.
	 *
	 * @param $_GET[slug] "SLUG" of the registry object to retrieve
	 * @param $_GET[registry_object_id] A specific registry object ID to fetch
	 * 
	 */
	public function getAncestryGraph()
	{
		$this->load->model('connectiontree');
		$this->load->model('registry_object/registry_objects','ro');

		$depth = 5;
		$this_registry_object = null;
		
		// Get the RO instance for this registry object so we can fetch its graphs
		if ($this->input->get('slug'))
		{
			$this_registry_object = $this->ro->getBySlug($this->input->get('slug'));
			$published_only = TRUE;
		}
		elseif ($this->input->get('registry_object_id'))
		{
			$this_registry_object = $this->ro->getByID($this->input->get('registry_object_id'));
			$published_only = FALSE;
		}

		if (!$this_registry_object)
		{
			throw new Exception("Unable to fetch connection graph for this registry object.");
		}

		// Loop through to get all immediate ancestors and build their trees
		$trees = array();
		$ancestors = $this->connectiontree->getImmediateAncestors($this_registry_object, $published_only);

		if ($ancestors)
		{
			foreach ($ancestors AS $ancestor_element)
			{
				if($this_registry_object->id != $ancestor_element['registry_object_id']){
					$root_element_id = $this->connectiontree->getRootAncestor($this->ro->getByID($ancestor_element['registry_object_id']), $published_only);
					$root_registry_object = $this->ro->getByID($root_element_id->id);

					// Only generate the tree if this is a unique ancestor
					if (!isset($this->connectiontree->recursed_children[$root_registry_object->id]))
					{
						$trees[] = $this->connectiontree->get($root_registry_object, $depth, $published_only, $this_registry_object->id);
					}
				}
			}
		}
		else
		{
			$trees[] = $this->connectiontree->get($this_registry_object, $depth, $published_only);
		}

		echo json_encode(array("status"=>"success", "trees"=>$trees));
	}


	public function getContributorPage()
	{
		$registry_object_id = $this->input->get('registry_object_id') ?: 0;
		$published_only = $this->input->get('published_only') ?: true;

		if (!$registry_object_id)
		{
			throw new Exception("Unable to get contributor page information: invalid ID");
		}

		$contributor_page_data = getContributorData();
		// XXX: go fetch this record with ->getByID()
		// XXX: Do some checking that this is actually a contributor page using a new model in data_sources/ ??
		// XXX: use the functions in the model to get the precanned values, from SOLR/wherever...
		// XXX: remember to pass along $published_only so draft contributor pages look reasonableish!

		echo json_encode(array("data" => $contributor_page_data));



	}
	public function getSlugFromKey()
	{
		$key = $this->input->get("key");
		
		$this->db->select("slug,registry_object_id,status")->from("registry_objects")->where("key",$key);
		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			$result = $query->result_array();
			echo json_encode($result);
		}
		else
		{
			echo json_encode(array());
		}
	}

	public function getThemePageIndex(){	
		$this->output->set_content_type(rda::response_format);
		$results = array();
		$this->load->model('apps/theme_cms/theme_pages');
		$pages = $this->theme_pages->get();
		foreach($pages as $p){
			if($p['visible']){
				$results[] = $p;
			}
		}
		$this->output->set_output(json_encode(array("items"=>$results)));
	}

	public function getThemePage($slug){
		$this->output->set_content_type(rda::response_format);
		$this->load->model('apps/theme_cms/theme_pages');
		$file = $this->theme_pages->get($slug);
		if($file){
			$this->output->set_output($file[0]['content']);
		}else{
			$this->output->set_output('File Not Found');
		}
	}

	public function getByList(){
		$this->load->model('registry_object/registry_objects','ro');
		$list = $this->input->post('list_ro');
		if(!$list){
			$data = file_get_contents('php://input');
			$array = json_decode($data);
			$list = $array->list_ro;
		}
		$ros = array();
		foreach($list as $key){
			$ro = $this->ro->getPublishedByKey($key);
			if($ro){
				$ros[] = array(
					'title'=>$ro->title,
					'id'=>$ro->id,
					'key'=>$ro->key,
					'slug'=>$ro->slug
				);
			}
		}
		// echo json_encode($ros);
		$this->output->set_output(json_encode(array('ros'=>$ros)));
	}

	public function getTagSuggestion($lcsh){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$q = $this->input->get('q');

		$result = array();

		$lcsh = ($lcsh=='true') ? true : false;

		if(!$lcsh){

			//get results from tags
			$this->db->select('name')->like('name', $q);
			$matches = $this->db->get_where('tags', array('type'=>'public'));
			foreach($matches->result() as $match){
				array_push($result, array(
					'name'=> $match->name,
					'source'=>'Public Tags'
				));
			}

			//get results from anzsrc-for
			$this->load->library('vocab');
			$matches = $this->vocab->anyContains($q, 'anzsrc-for');
			foreach($matches as $match){
				array_push($result, array(
					'name' => $match,
					'source' => 'ANZSRC-FOR'
				));
			}

			//get results from anzsrc-seo
			$this->load->library('vocab');
			$matches = $this->vocab->anyContains($q, 'anzsrc-seo');
			foreach($matches as $match){
				array_push($result, array(
					'name' => $match,
					'source' => 'ANZSRC-SEO'
				));
			}
		}

		//get results from lcsh
		// if($lcsh){
		// 	$this->load->library('solr');
		// 	$this->solr->setOpt('q', 'subject_type:lcsh');
		// 	$this->solr->setOpt('rows', 0);
		// 	$this->solr->setFacetOpt('pivot', 'subject_type,subject_value_resolved');
		// 	$this->solr->executeSearch();
		// 	$facet = $this->solr->getFacet();
		// 	$facet_pivot = $facet->{'facet_pivot'}->{'subject_type,subject_value_resolved'};
		// 	foreach($facet_pivot as $p){
		// 		if($p->{'value'}=='lcsh'){
		// 			foreach($p->{'pivot'} as $x){
		// 				similar_text(strtolower($x->{'value'}), strtolower($q), $percent);
		// 				if($percent > 50){
		// 					array_push($result, array(
		// 						'name' => $x->{'value'},
		// 						'source' => 'LCSH'
		// 					));
		// 				}
		// 			}
		// 		}
		// 	}
		// }

		echo json_encode($result);
	}

	public function addTag(){

		$key = $this->input->post('key');
		$tag = $this->input->post('tag');
		$user = $this->input->post('user');
		$user_from = $this->input->post('user_from');
		// echo $user.' from '.$user_from.' adding tag '.$tag.' to '.$key;

		if(!$key || !$tag || !$user || !$user_from){
			throw new Exception("An error has occured");
		}

		$this->load->model('registry_object/registry_objects','ro');
		$ro = $this->ro->getPublishedByKey($key);
		if($ro){
			if($ro->isSecret($tag)){
				throw new Exception('The tag '. $tag. ' is reserved. Please choose a different tag');
			}
			$ro->addTag($tag, 'public', $user, $user_from);
			$this->output->set_output(json_encode(array('status'=>'OK')));
		}else {
			throw new Exception("Unable to find registry object");
		}
	}

	public function syncRO(){
		$key = $this->input->post('key');
		$this->load->model('registry_object/registry_objects','ro');
		$ro = $this->ro->getPublishedByKey($key);
		if($ro){
			$ro->sync();
			$this->output->set_output(json_encode(array('status'=>'OK')));
		}else {
			throw new Exception("Unable to find registry object");
		}
	}

	public function getMatchingRecordsOnIdentifiersByID($id){
		$this->load->model('registry_object/registry_objects','ro');
		$ro = $this->ro->getByID($id);
		$content = '';
		if($ro) {
			$matching = $ro->findMatchingRecords();
			$content = array();
			foreach($matching as $ro_id){
				$ro = $this->ro->getByID($ro_id);
				array_push(
					$content,
					array(
						'id' => $ro->id,
						'title' => $ro->title,
						'slug' => $ro->slug,
						'group' => $ro->group
					)
				);

				unset($ro);
			}

			// Sort the matched records by name and group
			function sortByTitleAndGroup($a, $b)
			{
			    if ($a['title'] == $b['title'])
			    {
				return ($a['group'] < $b['group']) ? -1 : 1;
				}
				return ($a['title'] < $b['title']) ? -1 : 1;
			}
			usort($content,"sortByTitleAndGroup");

			$this->output->set_output(json_encode(array('status'=>'OK', 'content'=>$content)));
		} else {
			throw new Exception("Unable to find registry object");
		}
	}

	/* Setup this controller to handle the expected response format */
	public function __construct()
    {
    	parent::__construct();

    	// JSON output at all times?
    	//$this->output->set_content_type(rda::response_format);

    	// Set our exception handler to function in JSON mode
    	set_exception_handler('json_exception_handler');
    }
}