<?php

/**
 * Class Registry_object
 */
class Registry_object extends MX_Controller {

	private $components = array();

	/**
	 * Viewing a single registry object
	 * @return HTML generated by view
	 * @internal param $_GET ['id'] parsed through the dispatcher
	 * @todo  $_GET['slug'] or $_GET['any']
	 */
	function view(){


        $show_dup_identifier_qtip = true;
        $fl = '?fl';
        $useCache = true;
        $ro = null;
        if($this->input->get('useCache') == 'no'){
            $useCache = false;
        }
        $id = $this->input->get('id');
        $slug = $this->input->get('slug');
        $key = $this->input->get('key');
        $any = $this->input->get('any');


        if($any)
        {
            if(is_numeric($any))
                $id = $any;
            else
                $slug = $any;
        }

        if($id)
        {
            $ro = $this->ro->getByID($id, null, $useCache);
            if($ro && $ro->prop['status'] == 'success' && (!$slug || $slug != $ro->prop['core']['slug']))
            {
                redirect($ro->prop['core']['slug'].'/'.$id);
            }
        }

        if((!$ro || $ro->prop['status'] == 'error') && $slug){
            $ro = $this->ro->getBySlug($slug, null, $useCache);
            if($ro == 'MULTIPLE')
            {
                redirect('search/#!/slug='.$slug);
            }
            if($ro && $ro->prop['status'] == 'success')
            {
                redirect($slug.'/'.$ro->prop['core']['id']);
            }

        }

        if((!$ro || $ro->prop['status'] == 'error') && $key)
        {
            $ro = $this->ro->getByKey($key, $useCache);
            if($ro && $ro->prop->core['status'] == 'success'){
                redirect($ro->prop['core']['slug'].'/'.$ro->prop['core']['id']);
            }
        }

        $this->load->library('blade');
        if($this->input->get('fl') !== false)
        {
            $show_dup_identifier_qtip = false;
        }
        if($ro && $ro->prop['status'] == 'success')
        {
            $this->load->library('blade');

            $banner = asset_url('images/collection_banner.jpg', 'core');
            $theme = ($this->input->get('theme') ? $this->input->get('theme') : '2-col-wrap');
            $logo = $this->getLogo($ro->core['group']);
            $group_slug = url_title($ro->core['group'], '-', true);

            switch($ro->core['class']){
                case 'collection':
                    $render = 'registry_object/view';
                    break;
                case 'activity':
                    $render = 'registry_object/activity';
                    $theme = ($this->input->get('theme') ? $this->input->get('theme') : 'activity');
                    $banner =  asset_url('images/activity_banner.jpg', 'core');
                    break;
                case 'party':
                    $render = 'registry_object/party';
                    $theme = ($this->input->get('theme') ? $this->input->get('theme') : 'party');
                    break;
                case 'service':
                    $render = 'registry_object/service';
                    $theme = ($this->input->get('theme') ? $this->input->get('theme') : 'service');
                    break;
                default:
                    $render = 'registry_object/view';
                    break;
            }

            //record event
            $ro->event('viewed');
            ulog_terms(
                array(
                    'event' => 'portal_view',
                    'roid' => $ro->core['id'],
                    'roclass' => $ro->core['class'],
                    'dsid' => $ro->core['data_source_id'],
                    'group' => $ro->core['group'],
                    'ip' => $this->input->ip_address(),
                    'user_agent' => $this->input->user_agent()
                ),'portal', 'info'
            );

		    $this->blade
			->set('scripts', array('view', 'view_app', 'tag_controller'))
			->set('lib', array('jquery-ui', 'dynatree', 'qtip', 'map'))
			->set('ro', $ro)
			->set('contents', $this->components['view'])
			->set('aside', $this->components['aside'])
            ->set('view_headers', $this->components['view_headers'])
			->set('url', $ro->construct_api_url())
			->set('theme', $theme)
            ->set('logo',$logo)
            ->set('banner', $banner)
            ->set('group_slug',$group_slug)
            ->set('fl',$fl)
            ->set('show_dup_identifier_qtip', $show_dup_identifier_qtip)
			->render($render);
        }
        elseif(strpos($key, 'http://purl.org/au-research/grants/nhmrc/') !== false || strpos($key, 'http://purl.org/au-research/grants/arc/') !== false)
        {
			if(strpos($key, 'http://purl.org/au-research/grants/nhmrc/') !== false){
                $institution = 'National Health and Medical Research Council';
                $grantIdPos = strpos($key, 'nhmrc/') + 6;
                $grantId =	substr ($key, $grantIdPos);
                $purl = $key;
            }
            else{
                $institution = 'Australian Research Council';
                $grantIdPos = strpos($key, 'arc/') + 4;
                $grantId =	substr ($key, $grantIdPos);
                $purl = $key;
            }
            $this->blade
                ->set('scripts', array('view', 'grant_form'))
                ->set('lib', array('jquery-ui'))
                ->set('message', "NO ACTIVITY FOR YOU!!")
                ->set('institution', $institution)
                ->set('grantId', $grantId)
                ->set('purl', $purl)
                ->set('logo','http://researchdata.ands.org.au/assets/core/images/sad_smiley.png')
                ->render('soft_404_activity');
        }
        else{
            if(!$ro)
                $message = "NO RECORD FOUND";
            else
                $message = $ro->prop['status'].NL.$ro->prop['message'];
            $this->blade
                ->set('scripts', array('view'))
                ->set('lib', array('jquery-ui'))
                ->set('id', $this->input->get('id'))
                ->set('key', $this->input->get('key'))
                ->set('slug', $this->input->get('slug'))
                ->set('message', $message)
                ->set('logo','http://researchdata.ands.org.au/assets/core/images/sad_smiley.png')
                ->render('soft_404');
        }
	}

	function preview() {
		$this->load->library('blade');

		if ($this->input->get('ro_id')){
			$ro = $this->ro->getByID($this->input->get('ro_id'));
			$this->blade
				->set('ro', $ro)
				->render('registry_object/preview');
		} elseif($this->input->get('identifier_relation_id')) {

			//hack into the registry network and grab things
			//@todo: figure things out for yourself
			$rdb = $this->load->database('registry', TRUE);
			$result = $rdb->get_where('registry_object_identifier_relationships', array('id'=>$this->input->get('identifier_relation_id')));

			if ($result->num_rows() > 0) {
				$fr = $result->first_row();

				$ro = false;

				$pullback = false;
				//ORCID "Pull back"
				if($fr->related_info_type=='party' && $fr->related_object_identifier_type == 'orcid' && isset($fr->related_object_identifier)) {
					$pullback = $this->ro->resolveIdentifier('orcid', $fr->related_object_identifier);
					$filters = array('identifier_value'=>$fr->related_object_identifier);
					$ro = $this->ro->findRecord($filters);
				}

				$this->blade
					->set('record', $fr)
					->set('ro', $ro)
					->set('pullback', $pullback)
					->render('registry_object/preview-identifier-relation');
			}
		} else if ($this->input->get('identifier_doi')) {
			$identifier = $this->input->get('identifier_doi');
			
			//DOI "Pullback"
			$pullback = $this->ro->resolveIdentifier('doi', $identifier);
			$ro = $this->ro->findRecord(array('identifier_value'=>$identifier));

			$this->blade
				->set('ro', $ro)
				->set('pullback', $pullback)
				->render('registry_object/preview_doi');
		}
	}

	function vocab($vocab='anzsrc-for') {
		$uri = $this->input->get('uri');
		$data = json_decode(file_get_contents("php://input"), true);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$filters = $data['filters'];
		$this->load->library('vocab');
		if (!$uri) { //get top level
			$toplevel = $this->vocab->getTopLevel('anzsrc-for', $filters);
			// foreach ($toplevel['topConcepts'] as &$l) {
			// 	$r = array();
			// 	$result = json_decode($this->vocab->getConceptDetail('anzsrc-for', $l['uri']), true);
			// 	if(isset($result['result']['primaryTopic']['narrower'])){
			// 		foreach($result['result']['primaryTopic']['narrower'] as $narrower) {
			// 			$curi = $narrower['_about'];
			// 			$concept = json_decode($this->vocab->getConceptDetail('anzsrc-for', $curi), true);
			// 			$concept = array(
			// 				'notation' => $concept['result']['primaryTopic']['notation'],
			// 				'prefLabel' => $concept['result']['primaryTopic']['prefLabel']['_value'],
			// 				'uri' => $curi,
			// 				'collectionNum' => $this->vocab->getNumCollections($curi, array())
			// 			);
			// 			array_push($r, $concept);
			// 		}
			// 	}
			// 	$l['subtree'] = $r;
			// }
			echo json_encode($toplevel['topConcepts']);
		} else {
			$r = array();
			$result = json_decode($this->vocab->getConceptDetail('anzsrc-for', $uri), true);
			if(isset($result['result']['primaryTopic']['narrower'])){
				foreach($result['result']['primaryTopic']['narrower'] as $narrower) {
					$curi = $narrower['_about'];
					$concept = json_decode($this->vocab->getConceptDetail('anzsrc-for', $curi), true);
					$concept = array(
						'notation' => $concept['result']['primaryTopic']['notation'],
						'prefLabel' => $concept['result']['primaryTopic']['prefLabel']['_value'],
						'uri' => $curi,
						'collectionNum' => $this->vocab->getNumCollections($curi, $filters)
					);
					array_push($r, $concept);
				}
			}
			echo json_encode($r);
		}
	}

	function getSubjects() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$result = array();
		foreach($this->config->item('subjects') as $subject) {
			$slug = url_title($subject['display'], '-', true);
			foreach($subject['codes'] as $code) {
				$result[$slug][] = 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/'.$code;
			}
		}
		echo json_encode($result);
	}

	function resolveSubjects() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$data = json_decode(file_get_contents("php://input"), true);
		$subjects = $data['data'];

		$this->load->library('vocab');

		$result = array();

		if (is_array($subjects)) {
			foreach ($subjects as $subject) {
				$r = json_decode($this->vocab->getConceptDetail('anzsrc-for', 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/'.$subject), true);
				$result[$subject] = $r['result']['primaryTopic']['prefLabel']['_value'];
			}
		} else {
			$r = json_decode($this->vocab->getConceptDetail('anzsrc-for', 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/'.$subjects), true);
			$result[$subjects] = $r['result']['primaryTopic']['prefLabel']['_value'];
		}

		
		echo json_encode($result);
	}

	function addTag() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$data = json_decode(file_get_contents("php://input"), true);

		$data = $data['data'];
		$data['user'] = $this->user->name();
		$data['user_from'] = $this->user->authDomain();

		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,base_url().'registry/services/rda/addTag');//post to SOLR
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($ch);//execute the curl
		curl_close($ch);//close the curl

		echo $content;
	}

	/**
	 * Returns the stat of a record
	 * @param  int $id
	 * @return json
	 */
	function stat($id) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id);
		$stats = $ro->stat();

		echo json_encode($stats);
	}

    /**
     * increment the stats for a specified type by the value given
     * Returns the stat of a record
     * @param  int $id
     * @return json
     */
    function add_stat($id) {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $data = json_decode(file_get_contents("php://input"), true);
        $type = $data['data']['type'];
        $value = intval($data['data']['value']);
        $this->load->model('registry_objects', 'ro');
        $ro = $this->ro->getByID($id);
        $ro->event($type, $value);
        $stats = $ro->stat();

        echo json_encode($stats);
    }
	/**
	 * Search View
	 * Displaying the search view for the current component
	 * @return HTML 
	 */
	function search() {
		//redirect to the correct URL if q is used in the search query
		if($this->input->get('q')) {
			redirect('search/#!/q='.$this->input->get('q'));
		}

		$this->load->library('blade');
		$this->blade
			->set('lib', array('ui-events', 'angular-ui-map', 'google-map'))
			// ->set('scripts', array('search_app'))
			// ->set('facets', $this->components['facet'])
			->set('search', true) //to disable the global search
			->render('registry_object/search');
	}

	/**
	 * Main search function
	 * SOLR search
	 * @param bool $no_record
	 * @return json
	 * @internal param string $class class restriction
	 */
	function filter($no_log = false) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

		$data = json_decode(file_get_contents("php://input"), true);

		$filters = isset($data['filters']) ? $data['filters'] : false;

		// experiment with delayed response time
		// sleep(2);

		$this->load->library('solr');

		//restrict to default class
		$default_class = isset($filters['class']) ? $filters['class'] : 'collection';
		if(!is_array($default_class)) {
			$this->solr->setOpt('fq', '+class:'.$default_class);
		}

		$this->solr->setFilters($filters);

		//test
		// $this->solr->setOpt('fq', '+spatial_coverage_centres:*');

		//not recording a hit for the quick search done for advanced search
		if (!$no_log) {
			$event = array(
				'event' => 'portal_search',
				'ip' => $this->input->ip_address(),
				'user_agent' => $this->input->user_agent()
			);
			if($filters){
				$event = array_merge($event, $filters);
			}
			
			ulog_terms($event,'portal');
		}
		

		//returns this set of Facets
		
		if ($default_class=='activity')  {
			foreach($this->components['activity_facet'] as $facet){
				if ($facet!='temporal' && $facet!='spatial') $this->solr->setFacetOpt('field', $facet);
			}
		} elseif($default_class=='collection') {
			foreach($this->components['facet'] as $facet){
				if ($facet!='temporal' && $facet!='spatial') $this->solr->setFacetOpt('field', $facet);
			}
		}
		

		//high level subjects facet
		// $subjects = $this->config->item('subjects');
		// foreach ($subjects as $subject) {
		// 	$fq = '(';
		// 	foreach($subject['codes'] as $code) {
		// 		$fq .= 'subject_vocab_uri:("http://purl.org/au-research/vocabulary/anzsrc-for/2008/'.$code.'") ';
		// 	}
		// 	$fq.=')';
		// 	$this->solr->setFacetOpt('query', 
		// 		'{! key='.url_title($subject['display'], '-', true).'}'.$fq
		// 	);
		// }

		//temporal facet
		$this->solr
			->setFacetOpt('field', 'earliest_year')
			->setFacetOpt('field', 'latest_year')
			->setOpt('f.earliest_year.facet.sort', 'count asc')
			->setOpt('f.latest_year.facet.sort', 'count');


		//flags, these are the only fields that will be returned in the search
		$this->solr->setOpt('fl', 'id,type,title,description,group,slug,spatial_coverage_centres,spatial_coverage_polygons,administering_institution,researchers,matching_identifier_count,list_description');

		//highlighting
		$this->solr->setOpt('hl', 'true');
		$this->solr->setOpt('hl.fl', 'identifier_value_search, related_party_one_search, related_party_multi_search, group_search, related_info_search, subject_value_resolved_search, description_value, date_to, date_from, citation_info_search');
		$this->solr->setOpt('hl.simple.pre', '&lt;b&gt;');
		$this->solr->setOpt('hl.simple.post', '&lt;/b&gt;');

		//experiment hl attrs
		// $this->solr->setOpt('hl.alternateField', 'description');
		// $this->solr->setOpt('hl.alternateFieldLength', '100');
		// $this->solr->setOpt('hl.fragsize', '300');
		// $this->solr->setOpt('hl.snippets', '100');

		$this->solr->setFacetOpt('mincount','1');
		$this->solr->setFacetOpt('limit','100');
		$this->solr->setFacetOpt('sort','count');
		$result = $this->solr->executeSearch(true);

		//fuzzy search
		if($this->solr->getNumFound() == 0) {
			$new_search_term_array = explode(' ', escapeSolrValue($filters['q']));
			$new_search_term='';
			foreach($new_search_term_array as $c ){
				$new_search_term .= $c.'~0.7 ';
			}
			// $new_search_term = $data['search_term'].'~0.7';
			$this->solr->setOpt('q', 'fulltext:('.$new_search_term.') OR simplified_title:('.iconv('UTF-8', 'ASCII//TRANSLIT', $new_search_term).')');
			$result = $this->solr->executeSearch(true);
			if($this->solr->getNumFound() > 0){
				$result['fuzzy_result'] = true;
			}
		}

		$result['url'] = $this->solr->constructFieldString();

		echo json_encode($result);
	}

	/**
	 * List all attribute of a registry object
	 * @param $id
	 * @return json
	 */
	function get($id, $params='') {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');

		$params = explode('-', $params);
		if(empty($params)) $params = array('core');

	

		$this->load->model('registry_objects', 'ro');
		$ro = $this->ro->getByID($id, $params);
		echo json_encode($ro->prop);
	}

    /**
     * Get the logo url for a groups logo if it exists!
     * @param $group
     * @return string
     */
    function getLogo($group) {
        $this->load->model('group/groups','group');
        $logo = $this->group->fetchLogo($group);
        return $logo;
    }

	/**
	 * Construction
	 * Defines the components that will be displayed and search for within the application
	 */
	function __construct() {
		parent::__construct();
		$this->load->model('registry_objects', 'ro');
		$this->components = array(
			'view' => array('descriptions','reuse-list','quality-list','dates-list', 'connectiontree','related-objects-list' ,'spatial-info', 'subjects-list', 'related-metadata', 'identifiers-list'),
			'aside' => array('rights-info','contact-info'),
            'view_headers' => array('title','related-parties'),
			'facet' => array('spatial','group', 'license_class', 'type', 'temporal', 'access_rights'),
			'activity_facet' => array('type', 'activity_status', 'funding_scheme', 'administering_institution', 'funders')
		);
	}
}