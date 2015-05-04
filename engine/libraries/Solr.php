<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * SOLR class for use globally
 * Search functionality
 * Index functionality
 * @author : <minh.nguyen@ands.org.au>
 */
class Solr {

	private $CI;
	private $solr_url;
	private $result;
	private $options;
	private $multi_valued_fields;
	private $custom_query;

	/**
	 * Construction of this class
	 */
	function __construct(){
		$this->CI =& get_instance();
		$this->CI->load->library('session');
		$this->CI->load->helper('engine_helper');
		$this->init();
	}

	/**
	 * Initialize the solr class ready for call
	 * @return [type] [description]
	 */
	function init(){
		$this->solr_url = get_config_item('solr_url');
		$this->options = array('q'=>'*:*','start'=>'0','indent'=>'on', 'wt'=>'json', 'fl'=>'*', 'rows'=>'10');
		$this->multi_valued_fields = array('facet.field', 'fq', 'facet.query');
		$this->custom_query = false;
		return $this;
	}

	/**
	 * Manually set the option for solr search
	 * @param string $field 
	 * @param string $value
	 */
	function setOpt($field, $value){
		if(isset($this->options[$field])){
			if(is_array($this->options[$field])){
				array_push($this->options[$field], $value);
			}else{
				if(in_array($field, $this->multi_valued_fields)){
					$this->options[$field] = array($this->options[$field], $value);
				}else{
					$this->options[$field] = $value;
				}
			}
		}else{
		   $this->options[$field] = $value;
		}
		return $this;
	}

   /**
	 * Manually unsset the option for solr search
	 * @param string $field 
	 * @param string $value
	 */
	function clearOpt($field){
		if(isset($this->options[$field])){          
		   unset($this->options[$field]);
		}
		return $this;
	}

	/**
	 * get the existing option
	 * @param  string $field 
	 * @return value 
	 */
	function getOpt($field){
		if(isset($this->options[$field])){
			return $this->options[$field];
		}else return null;
	}

	/**
	 * Pass in a custom query to use, ignore all filters
	 * @param string $query 
	 */
	function setCustomQuery($query){
		$this->custom_query = $query.'&wt=json';
		return $this;
	}

	/**
	 * Return the custom query for inspection
	 * @return string custom_query
	 */
	function getCustomQuery(){
		return $this->custom_query;
	}

	/**
	 * Return all of the options, mainly for debugging
	 * @return array of SOLR options
	 */
	public function getOptions(){
		return $this->options;
	}

	 /**
	 * Manually set the facet option for solr search (and enable the facet functionality)
	 * @param string $field 
	 * @param string $value
	 */
	function setFacetOpt($field, $value=null){
		$this->setOpt('facet','true');
		$this->setOpt('facet.' . $field, $value);
		return $this;
	}


	/**
	 * Manually set the solr url
	 * @param string $value http link for solr url, defaults to be the value in the config
	 */
	function setSolrUrl($value){
		$this->solr_url = $value;
		return $this;
	}


	/**
	 * return the total numFound of the search result
	 * @return integer
	 */
	function getNumFound(){
		if(isset($this->result->{'response'}->{'numFound'})){
			return ((int) ($this->result->{'response'}->{'numFound'}));
		}else if(isset($this->result['response']['numFound'])){
			return ((int) $this->result['response']['numFound']);
		} else {
			return 0;
		}
	}

	/**
	 * get SOLR result header
	 * @return array 
	 */
	function getHeader(){
		return $this->result->{'responseHeader'};
	}

	/**
	 * get SOLR result response
	 * @return array 
	 */
	function getResult(){
		if(isset($this->result->{'response'})){
			return $this->result->{'response'};
		}else{
			return false;
		}
	}

	function getFacet(){
		if(isset($this->result->{'facet_counts'})){
			return $this->result->{'facet_counts'};
		}else return false;
	}

	/**
	 * get SOLR facet query response by field name
	 * @param  string $facet_field the name of a facet field (earlier instantiated with setOpt())
	 * @return array 
	 */
	function getFacetResult($facet_field){
		if (isset($this->result->facet_counts->facet_fields->{$facet_field}))
		{
			// Sort the pairs (they arrive in list form, we want them as value=>count tuples)
			$value_pair_list = $this->result->facet_counts->facet_fields->{$facet_field};
			$tuples = array();
			for ($i=0; $i<count($value_pair_list)-1; $i+=2)
			{
				$tuples[$value_pair_list[$i]] = $value_pair_list[$i+1];
			}
		   // echo $facet_field;
		  //  print_r($tuples);
			return $tuples;
		}
		else
		{
			return array();
		}
	}
	
	/**
	 * Sample simple search
	 * @param  string $term a full text search on this term
	 * @return array
	 */
	function search($term){
		$this->options['q']='+fulltext:'.$term;
		return $this->executeSearch();
	}

	/**
	 * Add query condition
	 * @param  string $condition add a query condition to this request (appends to q=)
	 */
	function addQueryCondition($condition){
		$this->options['q'].=' '. $condition;
	}

	function addBoostCondition($condition){
		if(isset($this->options['bq'])){
			$this->options['bq'].=' '.$condition;
		}else{
			$this->options['bq'] = $condition;
		}
		
	}

	function setBrowsingFilter(){
		$this->setOpt('sort', 'list_title_sort asc');
	}

	// Takes an array of user-defined filters and crunches them into
	// an ANDS-specific SOLR query (including ranking, field names, etc)
	function setFilters($filters){

		$CI =& get_instance();
		$CI->load->library('vocab');



		// Use SOLR's extended disMax query type - https://wiki.apache.org/solr/ExtendedDisMax
		// (more forgiving query parsing for user input -- allows syntatically incorrect boolean queries)
		$this->setOpt('defType', 'edismax');

		// Some pagination variables (defaults to 15 rows if not specified)
		$page = 1; $start = 0;
		$pp = ( isset($filters['rows']) ? (int) $filters['rows'] : 15 );
		$this->setOpt('rows', $pp);

		// Default query if none specified (fetches all records)
		$this->setOpt('q.alt', '*:*');
		

		if (isset($filters['q']))
		{
			$this->setOpt('sort', "score desc");
		} 
		else
		{
			$filters['q'] = "";
			$this->setOpt('sort', "list_title_sort asc");
		}









		// By default, also bring back the score in results (overridden if fl filter set)
		$this->setOpt('fl', '*, score'); 

		// Remove variations of "Australia" from the search query unless the query contains quotes
		if ($filters['q'] && substr_count('"', $filters['q']) == 0)
		{
			$filters['q'] = preg_replace('/(austral.*?)[\\s\\z]/i',"", $filters['q']);
		}

		// Filter records that match the search terms (boost according to where the terms match)
		$this->setOpt('qf', 'title_search^1 alt_title_search^0.9 description_value~10^0.01 description_value^0.05 identifier_value^0.05 tag_search^0.05 fulltext^0.00001 related_party_one_search^0.2');

		// Amount of slop applied to phrases in the user's query string filter (1 = 1 word apart)
		// Disable slopping for exact phrase search
		if($filters['q'] && substr_count('"', $filters['q']) != 0){
			$this->setOpt('qs', '1');
		}
		
		// $this->setOpt('q.op', 'AND');
		
		
		// Score boosting applied to phrases based on how many parts of the phrase match
		$this->setOpt('pf', 'title_search^5 alt_title_search^4 description_value^0.5 related_party_one_search^1');
		$this->setOpt('pf2', 'title_search^20 alt_title_search^18 description_value^5 description_value~5^3 related_party_one_search^2');
		$this->setOpt('pf3', 'title_search^100 alt_title_search^90 description_value^25 description_value~5^5 related_party_one_search^3');

		// Default amount of "slop" on phrase queries (applied to pf, pf2, pf3 if not overriden by tilde)
		$this->setOpt('ps', '2');

		// Whether to break equal-scored records using the maximum of individual score components (~0.0) or sum (~1.0)
		$this->setOpt('tie', '0.1');

		// map each of the user-supplied filters to it's corresponding SOLR parameter
		foreach($filters as $key=>$value){
			if(!is_array($value) && $key!='q'){
				$value = $this->escapeInvalidXmlChars($value);
			} 
			switch($key){
				case 'rq':
					$this->clearOpt('defType');//returning to the default deftype
					$this->setOpt('q', $value);
				break;
				case 'q': 
					// $value = $this->escapeSolrValue($value);
					// if(trim($value)!="") $this->setOpt('q', 'fulltext:('.$value.') OR simplified_title:('.iconv('UTF-8', 'ASCII//TRANSLIT', $value).')');
					if((strpos($value, 'AND')!==false) && (strpos($value, 'OR')!==false)) {
						if(trim($value)!="") $this->setOpt('q', '{!q.op=AND}'.$value);
					} else {
						if(trim($value)!="") $this->setOpt('q', $value);
					}
				break;
                case 'after_date':
                    date_default_timezone_set("UTC");
                    $date = date("Y-m-d\TH:i:s\Z",$value);
                    $this->setOpt('fq', 'record_modified_timestamp:['.$date.' TO *]');
                    break;
                case 'before_date':
                    date_default_timezone_set("UTC");
                    $date = date("Y-m-d\TH:i:s\Z",$value);
                    $this->setOpt('fq', 'record_modified_timestamp:[* TO '.$date.']');
                    break;
				case 'p': 
					$page = (int)$value;
					if($page>1){
						$start = $pp * ($page-1);
					}
					$this->setOpt('start', $start);
					break;
				case 'class': 
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' class:('.$v.')'; 
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', '+class:('.$value.')');
					}
					break;
				case 'group': 
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' group:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						$this->setOpt('fq', '+group:("'.$value.'")');
					}
					break;
				case 'type': 
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' type:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', '+type:("'.$value.'")');
					}
					break;
				case 'subject_value_resolved': 
				   if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' subject_value_resolved:('.$v.')'; 
						$this->setOpt('fq', $fq_str);
					}else{
					   if($value!='all') $this->setOpt('fq', '+s_subject_value_resolved:("'.$value.'")');
					}
					break;
				case 's_subject_value_resolved': 
					$this->setOpt('fq', '+s_subject_value_resolved:("'.$value.'")');
					break;
				case 'subject_vocab_uri':
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) {
							$v = rawurldecode($v);
							$fq_str .= ' subject_vocab_uri:("'.$v.'")'; 
						}
						$this->setOpt('fq', $fq_str);
					}else{
						$s = json_decode($CI->vocab->getConceptDetail('anzsrc-for', $value), true);
						if($s){
							$label = $s['result']['primaryTopic']['prefLabel']['_value'];
							$this->setOpt('fq', '(subject_vocab_uri:("'.$value.'") OR tag:("'.$label.'"))');
						}else{
							if($value!='all') $this->setOpt('fq', '+subject_vocab_uri:("'.$value.'")');
						}
					}
					break;
				case 'temporal':
					$date = explode('-', $value);
					$this->setOpt('fq','+earliest_year:['.$date[0].' TO *]');
					$this->setOpt('fq','+latest_year:[* TO '.$date[1].']');
					break;
				case 'year_from':
                    if($value!='')
					$this->setOpt('fq','earliest_year:['.$value.' TO *]');
					break;
				case 'year_to':
                    if($value!='')
					$this->setOpt('fq','latest_year:[* TO '.$value.']');
					break;
				case 'license_class': 
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' license_class:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', '+license_class:("'.$value.'")');
					}
					break;
				case 'spatial':
					$this->setOpt('fq','+spatial_coverage_extents:"Within('.$value.')"');
					break;
				case 'map':
					$this->setOpt('fq','+spatial_coverage_area_sum:[0.00001 TO *]');
					if (isset($filters['rows']) && is_numeric($filters['rows'])){
						$this->setOpt('rows', $filters['rows']);
					}else{
						$this->setOpt('rows', 1500);
					}
					$this->setOpt('fl', 'id,spatial_coverage_area_sum,spatial_coverage_centres,spatial_coverage_extents,spatial_coverage_polygons');
					break;
				case 'boost_key':
					if(is_array($value)){
						$weight = 1000;
						foreach($value as $v){
							$this->addQueryCondition(' OR key:("'.$v.'")^'.$weight);
							$weight = $weight - 10;
						}
					}else{
						$this->addQueryCondition(' OR key:("'.$value.'")^100');
					}
					break;
				case 'fl':
					$this->setOpt('fl', $value);
					break;
				case 'slug':
					$this->setOpt('fq', '+slug:('.$value.')');
					break;
				case 'key':
					$this->setOpt('fq', '+key:("'.$value.'")');
					break;
				case 'tag':
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' tag:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						$resolved_url = $CI->vocab->resolveLabel($value, 'anzsrc-for');
						if($resolved_url){
							$resolved_url = $resolved_url['about'];
							if($value!='all') {
								$this->setOpt('fq', '(tag:("'.$value.'") OR subject_vocab_uri:("'.$resolved_url.'"))');
							}else{
								if($value!='all') $this->setOpt('fq', '+tag:("'.$value.'")');
							}
						}else{
							if($value!='all') $this->setOpt('fq', '+tag:("'.$value.'")');
						}
					}
					break;
				case 'originating_source':
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' originating_source:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', '+originating_source:("'.$value.'")');
					}
					break;
				case 'data_source_key':
					if(is_array($value)){
						$fq_str = '';
						$fq_str = implode('") OR data_source_key:("', $value);
						$fq_str = 'data_source_key:("'.$fq_str.'")';
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', 'data_source_key:("'.$value.'")');
					}
					break;
				case 'related_object_id':
					$this->setOpt('fq','+related_object_id:'.$value.'');
					break;
				case 'identifier_value':
					if(is_array($value)){
						$identifier_search_query = join('","', $value);
						$identifier_search_query = '+identifier_value:("'.$identifier_search_query.'")';
						$this->setOpt('fq', $identifier_search_query);
					}else{
						$this->setOpt('fq', '+identifier_value:("'.$value.'")');
					}
					break;
				case 'keywords': 
				case 'scot':
				case 'pont':
				case 'psychit':
				case 'anzsrc':
				case 'apt':
				case 'gcmd':
				case 'lcsh':
				case 'subject_value':
					if(is_array($value)){
						$subject_search_query = join('" OR subject_value_resolved_search:"', $value);
						$subject_search_query = "(subject_value_resolved_search:\"" .$subject_search_query."\")";
						$this->setOpt('fq', $subject_search_query);
					}else{
						$this->setOpt('fq', '+subject_value_resolved_search:("'.$value.'")');
					}
					break;
				case 'not_id':
					$this->setOpt('fq', '-id:'.$value.'');
					break;
				case 'not_related_object_id':
					$this->setOpt('fq', '-related_object_id:'.$value.'');
					break;
				case 'sort':
					$this->setOpt('sort', $value);
					break;
				case 'limit':
					$this->setOpt('rows', $value);
					break;
				case 'random':
					$this->setOpt('sort', 'random_'.rand(1,255642).' desc');
					break;
				case 'refine':
					if(is_array($value)){
						foreach($value as $v) {
							$this->setOpt('fq', '+(title_search:("'.$v.'") description_value:("'.$v.'"))');
						}
					}else{
						$this->setOpt('fq', '+(title_search:("'.$value.'") description_value:("'.$value.'"))');
					}
					break;
				case 'access_right':
				case 'access_rights':
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' access_rights:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', '+access_rights:("'.$value.'")');
					}
					break;
				case 'related_party_one_id':
					$this->setOpt('fq', '+related_party_one_id:"'.$value.'"');
					break;
				case 'related_party_multi_id':
					$this->setOpt('fq', '+related_party_multi_id:"'.$value.'"');
					break;
				case 'related_collection_id':
                    if(is_array($value)){
                        $fq_str = '';
                        foreach($value as $v)
                            $fq_str .= ' related_collection_id:("'.$v.'")';
                        $this->setOpt('fq', $fq_str);
                    }else{
					    $this->setOpt('fq', '+related_collection_id:"'.$value.'"');
                    }
					break;
				case 'related_service_id':
					$this->setOpt('fq', '+related_service_id:"'.$value.'"');
					break;
				case 'related_activity_id':
					$this->setOpt('fq', '+related_activity_id:"'.$value.'"');
					break;
				case 'subject':
					if(is_array($value)) {
						$fq_str = '';
						foreach($value as $v) $fq_str .= $this->formatSubjectsArrayFilters($v).' ';
						$this->setOpt('fq', $fq_str);
					} else {
						$this->setOpt('fq', $this->formatSubjectsArrayFilters($value));
					}
					break;
				case 'anzsrc-for': 
					if(is_array($value)) {
						$fq_str = '(';
						foreach($value as $v) $fq_str .= ' subject_vocab_uri:("http://purl.org/au-research/vocabulary/anzsrc-for/2008/'.$v.'")';
						$fq_str .= ')';
						$this->setOpt('fq', $fq_str);
					} else {
						$this->setOpt('fq', '+subject_vocab_uri:("http://purl.org/au-research/vocabulary/anzsrc-for/2008/'.$value.'")');
					}
					break;
				case 'anzsrc-seo': 
					if(is_array($value)) {
						$fq_str = '(';
						foreach($value as $v) $fq_str .= ' subject_vocab_uri:("http://purl.org/au-research/vocabulary/anzsrc-seo/2008/'.$v.'")';
						$fq_str .= ')';
						$this->setOpt('fq', $fq_str);
					} else {
						$this->setOpt('fq', '+subject_vocab_uri:("http://purl.org/au-research/vocabulary/anzsrc-seo/2008/'.$value.'")');
					}
					break;

				case 'title':
					if(!$filters['q']) $this->setOpt('q', $value);
					$this->setOpt('fq', '+title_search:('.$value.')');
					break;
				case 'description':
					if ($value) {
						if(!$filters['q']) $this->setOpt('q', $value);
						$this->setOpt('fq', '+description_value:('.$value.')');
					}
					break;
				case 'identifier':
					if ($value) {
						if(!$filters['q']) $this->setOpt('q', $value);
						$this->setOpt('fq', '+identifier_value_search:('.$value.')');
					}
					break;
				case 'related_people':
					if ($value) {
						if(!$filters['q']) $this->setOpt('q', $value);
						$this->setOpt('fq', '+related_party_one_search:('.$value.')');
					}
					break;
				case 'related_organisations':
					if ($value) {
						if(!$filters['q']) $this->setOpt('q', $value);
						$this->setOpt('fq', '+related_party_multi_search:('.$value.')');
					}
					break;
				case 'administering_institution':
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' administering_institution:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', '+administering_institution:("'.$value.'")');
					}
					break;
				case 'institution':
					if ($value) {
						if(!$filters['q']) $this->setOpt('q', $value);
						$this->setOpt('fq', 'administering_institution_search:('.$value.')');
					}
					break;
				case 'researcher':
					if ($value) {
						if(!$filters['q']) $this->setOpt('q', $value);
						$this->setOpt('fq', 'researchers_search:('.$value.')');
					}
					break;
				case 'funding_from':
					$funding_from = $value;
					if (isset($filters['funding_to'])) {
						$funding_to = $filters['funding_to'];
					} else $funding_to = '*';
					$this->setOpt('fq','funding_amount:['.$funding_from.' TO '.$funding_to.']');
					break;
				case 'funding_to' :
					$funding_to = $value;
					if (isset($filters['funding_from'])) {
						$funding_from = $filters['funding_from'];
					} else $funding_from = '*';
					$this->setOpt('fq','funding_amount:['.$funding_from.' TO '.$funding_to.']');
					break;
				case 'commence_from':
					$commence_from = $value;
					$commence_to = isset($filters['commence_to']) ? $filters['commence_to'] : '*';
					$this->setOpt('fq','earliest_year:['.$commence_from.' TO '.$commence_to.']');
					break;
				case 'commence_to':
					$commence_to = $value;
					$commence_from = isset($filters['commence_from']) ? $filters['commence_from'] : '*';
					$this->setOpt('fq','earliest_year:['.$commence_from.' TO '.$commence_to.']');
					break;
				case 'completion_from':
					$completion_from = $value;
					$completion_to = isset($filters['completion_to']) ? $filters['completion_to'] : '*';
					$this->setOpt('fq','latest_year:['.$completion_from.' TO '.$completion_to.']');
					break;
				case 'completion_to':
					$completion_to = $value;
					$completion_from = isset($filters['completion_from']) ? $filters['completion_from'] : '*';
					$this->setOpt('fq','latest_year:['.$completion_from.' TO '.$completion_to.']');
					break;
				case 'funding_scheme':
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' funding_scheme:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', '+funding_scheme:("'.$value.'")');
					}
					break;
				case 'funders':
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' funders:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', '+funders:("'.$value.'")');
					}
					break;
				case 'activity_status':
					if(is_array($value)){
						$fq_str = '';
						foreach($value as $v) $fq_str .= ' activity_status:("'.$v.'")'; 
						$this->setOpt('fq', $fq_str);
					}else{
						if($value!='all') $this->setOpt('fq', '+activity_status:("'.$value.'")');
					}
					break;
			}
		}
		return $this;
	}

	function formatSubjectsArrayFilters($value) {
		$fq = ' (';
		$subjects = $this->CI->config->item('subjects');

		$subject = false;
		foreach($subjects as $item) {
			if(url_title($item['display'], '-', true)==$value) {
				$subject = $item;
			}
		}
		if(!$subject) {
			return '';
		} else {
			foreach($subject['codes'] as $code) {
				$fq.='subject_vocab_uri:("http://purl.org/au-research/vocabulary/anzsrc-for/2008/'.$code.'") ';
			}
			
			$fq.=')';
			return $fq;
		}
		
	}

	function formatSolrArray($array, $type) {
		$str = '';
		foreach($array as &$a) {
			$a = $type.':('.$a.')';
		}
		$str = implode($array, ' OR ');
		return $str;
	}

	/**
	 * Construct a field string based on SOLR OPTIONS, for posting
	 * @return string fields_string
	 */
	function constructFieldString(){
		$fields_string='';
		foreach($this->options as $key=>$value) {
			if(is_array($value)){
				foreach($value as $v){
				   $fields_string .= $key.'='.rawurlencode($v).'&';
				}
			}else{
				$fields_string .= $key.'='.rawurlencode($value).'&';
			}
		}//build the string
		return $fields_string;
	}

	/**
	 * Execute the search based on the given options
	 * @return array results
	 */
	function executeSearch($as_array = false){
		if($this->custom_query){
			$content = $this->post($this->custom_query, 'select');
		}else {
			$content = $this->post($this->constructFieldString(), 'select');
		}

		$json = json_decode($content, $as_array);
		if($json){
			$this->result = $json;
			return $this->result;
		}else{
			throw new Exception('SOLR Query failed....ERROR:'.$content.'<br/> QUERY: '.$this->constructFieldString());
		}
	}

	function escapeSolrValue($string){
		//$string = urldecode($string);
		// + - && || ! ( ) { } [ ] ^ " ~ * ? : \ /
		$match = array('\\','&', '|', '!', '(', ')', '{', '}', '[', ']', '^', '~', '*', '?', ':', '/');
		$replace = array('\\\\','&', '\\|', '\\!', '\\(', '\\)', '\\{', '\\}', '\\[', '\\]', '\\^', '\\~', '\\*', '\\?', '\\:', '\\/');
		$string = str_replace($match, $replace, $string);

		if(substr_count($string, '"') % 2 != 0){
			$string = str_replace('"', '\\"', $string);
		}

		return $string;
	}


	/*
	since we post xml to solr for indexing, all invalid characters are escaped by the xml serializer.
	so to get a string match we must escape the search query as well.
	*/

	function escapeInvalidXmlChars($urlComp)
	{
		$findArray = array("&", "<", ">", ":");
		$replaceArray = array("&amp;", "&lt;", "&gt;", "\:");
		$value = rawurldecode($urlComp);
		return str_replace($findArray, $replaceArray, $value);
	}

	/**
	 * Post a set of documents to SOLR
	 * @param  string of xml $docs   
	 * @param  string $handle [select|update]
	 * @return json|xml return values
	 */
	function post($docs, $handle='select'){
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$this->solr_url.$handle);//post to SOLR
		//curl_setopt($ch,CURLOPT_POST,count($fields));//number of POST var
		curl_setopt($ch,CURLOPT_POSTFIELDS,$docs);//post the field strings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
		$content = curl_exec($ch);//execute the curl
		curl_close($ch);//close the curl
		return $content;
	}

	function addDoc($docs){
		return curl_post($this->solr_url.'update?wt=json', $docs);
	}

	function add_json($docs){
		return curl_post($this->solr_url.'update/?wt=json', $docs, array("Content-Type: application/json; charset=utf-8"));
	}

	function commit(){
		return curl_post($this->solr_url.'update?wt=json&commit=true', '<commit waitSearcher="false"/>');
	}

	function deleteByQueryCondition($query_condition)
	{
		if ($query_condition)
		{
			$result = curl_post($this->solr_url.'update?commit=true&wt=json',
									 '<delete><query>'.$query_condition.'</query></delete>');    
			return $result;
		}
	}


	function deleteByIDsCondition($ids)
	{
		$counter = 0;
		$result = '';
		$query = '';    

		if (is_array($ids))
		{
			$chunkSize = 1000;
			$arraySize = count($ids);
	
			foreach($ids as $id)
			{
				$counter++;
				$query .= 'id:'.$id.' ';
				if($counter % $chunkSize === 0)
				{
					$result .= $this->deleteByQueryCondition($query);
					$query = '';
				}
			}
			$result .= $this->deleteByQueryCondition($query);
		}

		return $result;
	}

	function deleteByID($id){
		$result = false;
		if($id){
			 $result = $this->deleteByQueryCondition('id:'.$id);
		}
		return $result;
	}


	function clear($data_source_id='all'){
		if($data_source_id!='all'){
			$query = 'data_source_id:("'.$data_source_id.'")';
		}else{
			$query = '*:*';
		}

		$result = curl_post($this->solr_url.'update?commit=true&wt=json', '<delete><query>'.$query.'</query></delete>');    
		//$result .= curl_post($this->solr_url.'update?optimize=true', '<optimize waitFlush="false" waitSearcher="false"/>');
		return $result; 
		//return curl_post($this->solr_url.'update?wt=json&commit=true', '<delete>'.$query.'</delete>');
	}
}