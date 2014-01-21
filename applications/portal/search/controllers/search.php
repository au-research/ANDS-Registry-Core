<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Search extends MX_Controller {

	function index(){
		$data['title']='Search - Research Data Australia';
		$data['scripts'] = array('search','infobox');
		$data['js_lib'] = array('google_map', 'range_slider','vocab_widget','qtip');

		$this->load->library('stats');
		$this->stats->registerPageView();		
		
		$this->load->view('search_layout', $data);
	}

	function filter(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$filters = ($this->input->post('filters') ? $this->input->post('filters') : false);
		if(!$filters){
			$data = file_get_contents("php://input");
			$array = json_decode(file_get_contents("php://input"), true);
			$filters = $array['filters'];
		}
		$data = $this->solr_search($filters, true);
		//return the result to the client
		echo json_encode($data);
	}

	function solr_search($filters, $include_facet = true){
		$this->load->library('solr');

		//optional facets return, true for rda search
		if($include_facet){
			$facets = array(
				'class' => 'Class',
				'group' => 'Contributor',
				'license_class' => 'Licence',
				'type' => 'Type',
			);
			foreach($facets as $facet=>$display){
				$this->solr->setFacetOpt('field', $facet);
			}
			$this->solr->setFacetOpt('mincount','1');
			$this->solr->setFacetOpt('limit','100');
			$this->solr->setFacetOpt('sort','count');
		}

		//boost
		// $this->solr->setOpt('bq', 'id^1 group^0.8 display_title^0.5 list_title^0.5 fulltext^0.2 (*:* -group:("Australian Research Council"))^3  (*:* -group:("National Health and Medical Research Council"))^3');
		// $this->solr->setOpt('bq', '(*:* -group:("Australian Research Council"))^3  (*:* -group:("National Health and Medical Research Council"))^3');
		if($filters){
			$this->solr->setFilters($filters);
		}else{
        	$this->solr->setBrowsingFilter();
        }

		$data['search_term'] = (isset($filters['q']) ? $filters['q'] : '');

		$this->solr->executeSearch();

		//if no result is found, forsake the edismax and thus forsake the boost query and search again
		// unicode characters
		if($this->solr->getNumFound()==0){
			$this->solr->clearOpt('defType');
			$this->solr->clearOpt('bq');
			$this->solr->executeSearch();
		}

		//if still no result is found, do a fuzzy search, store the old search term and search again
		if($this->solr->getNumFound()==0 && isset($filters['q']) && trim($filters['q'])!=''){
			$new_search_term_array = explode(' ', escapeSolrValue($filters['q']));
			$new_search_term='';
			foreach($new_search_term_array as $c ){
				$new_search_term .= $c.'~0.7 ';
			}
			// $new_search_term = $data['search_term'].'~0.7';
			$this->solr->setOpt('q', 'fulltext:('.$new_search_term.') OR simplified_title:('.iconv('UTF-8', 'ASCII//TRANSLIT', $new_search_term).')');
			$this->solr->executeSearch();
			if($this->solr->getNumFound() > 0){
				$data['fuzzy_result'] = true;
			}
		}

		//give up, cry a lot
		if($this->solr->getNumFound()==0){
			$data['no_result'] = true;
		}else{
			//continue on life
			$data['has_result'] = true;
		}

		//continue on life
		
		/**
		 * Getting the results back
		 */
		$data['result'] = $this->solr->getResult();
		$data['numFound'] = $this->solr->getNumFound();
		$data['solr_header'] = $this->solr->getHeader();
		$data['timeTaken'] = $data['solr_header']->{'QTime'} / 1000;

		/**
		 * House cleaning on the facet_results
		 */
		$data['facet_result'] = array();
		foreach($facets as $facet=>$display){
			$facet_values = array();
			$solr_facet_values = $this->solr->getFacetResult($facet);
			if(count($solr_facet_values)>0){
				if(isset($filters['facetsort']) && $filters['facetsort']=='alpha') uksort($solr_facet_values, "strnatcasecmp");
				foreach($solr_facet_values AS $title => $count){
					if($count>0){
						$facet_values[] = array(
							'title' => $title,
							'count' => $count
						);
					}
				}
				array_push($data['facet_result'], array('label'=>$display, 'facet_type'=>$facet, 'values'=>$facet_values));
				if($facet=='class'){
					$data['selected_tab'] = $facet;
				}
			}else if(isset($filters[$facet])){//for selected facet, always display this
				$facet_values[] = array('title'=>$filters[$facet], 'count'=>0);
				array_push($data['facet_result'], array('label'=>$display, 'facet_type'=>$facet, 'values'=>$facet_values));
			}
		}

		/**
		 * Pagination prep
		 * Page: {{page}}/{{totalPage}} |  <a href="#">First</a>  <span class="current">1</span>  <a href="#">2</a>  <a href="#">3</a>  <a href="#">4</a>  <a href="#">Last</a>
		 */
		$page = (isset($filters['p']) ? ((int) $filters['p']) : 1);
		$pp = ( isset($filters['rows']) ? (int) $filters['rows'] : 15 );
		$range = 3;
		$pagi = '';
		$pagi .= '<div class="page_navi">';
		$pagi .=  'Page: '.$page.'/'.ceil($data['numFound'] / $pp).'   |  ';
		$pagi .=  '<a href="javascript:void(0);" class="filter" filter_type="p" filter_value="1">First</a>';
		// if($page > 1){
		// 	$pagi .=  '<a href="javascript:void(0);"> &lt;</a>';
		// }
		for ($x = ($page - $range); $x < (($page + $range) + 1); $x++) {
			if (($x > 0) && ($x <= ceil($data['numFound'] / $pp))) { //if it's valid
				if($x==$page){//if we're on current
					$pagi .=  '<a href="javascript:;" class="current filter" filter_type="p" filter_value="'.$x.'">'.$x.'</a>';
				}else{//not current
					$pagi .=  '<a href="javascript:;" class="filter" filter_type="p" filter_value="'.$x.'">'.$x.'</a>';
				}
			}
		}
		// if not on last page, show Next
		// if($page < ceil($data['numFound'] / $pp)){
		// 	$pagi .=  '<a href="javascript:void(0);">&gt;</a>';
		// }
		$pagi .=  '<a href="javascript:void(0);" class="filter" filter_type="p" filter_value="'.ceil($data['numFound'] / $pp).'">Last</a>';
		$pagi .=  '</div>';
		$data['pagination'] = $pagi;

		$data['options'] = $this->solr->getOptions();
		$data['facet_counts'] = $this->solr->getFacet();
		$data['fieldstrings'] = $this->solr->constructFieldString();




		return $data;
	}

	function suggest(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		$search = $this->input->get('q');
		$terms = $this->stats->getSearchSuggestion($search);
		echo json_encode($terms);
	}

	function registerSearchTerm(){
		$search_term = $this->input->get('q');
		$this->stats->registerSearchTerm($this->input->post('term'),$this->input->post('num_found'));
	}

	function getAllSubjects($vocab_type){
		$filters = $this->input->post('filters');
		$subjects_categories = $this->config->item('subjects_categories');
		$list = $subjects_categories[$vocab_type]['list'];
		$result = array();
		foreach($list as $l){
			$result_type = $this->getAllSubjectsForType($l, $filters);
			$result_list = (isset($result_type['list']) ? $result_type['list'] : array());
			$result = array_merge($result, $result_list);
		}

		$azTree = array();
		$azTree['0-9']=array('subjects'=>array(), 'total'=>0, 'display'=>'0-9');
		foreach(range('A', 'Z') as $i){$azTree[$i]=array('subjects'=>array(), 'total'=>0, 'display'=>$i);}

		foreach($result as $r){
			if(ctype_alnum($r['value'])){
				$first = strtoupper($r['value'][0]);
				if(is_numeric($first)){$first='0-9';}
				$azTree[$first]['total']++;
				array_push($azTree[$first]['subjects'], $r);
			}
		}
		$data['azTree'] = $azTree;
		$this->load->view('subjectfacet-tree', $data);
	}

	function getAllSubjectsForType($type, $filters){
		$this->load->library('solr');
		$this->solr->setOpt('q', '*:*');
		$this->solr->setOpt('defType', 'edismax');
		$this->solr->setOpt('mm', '3');
		$this->solr->setOpt('q.alt', '*:*');
		$this->solr->setOpt('fl', '*, score');
		$this->solr->setOpt('qf', 'id^1 group^0.8 display_title^0.5 list_title^0.5 fulltext^0.2');
		$this->solr->setOpt('rows', '0');

		$this->solr->clearOpt('fq');

		if($filters){
            $this->solr->setFilters($filters);
        }else{
        	$this->solr->setBrowsingFilter();
        }
        $this->solr->addQueryCondition('+subject_type:"'.$type.'"');
		$this->solr->setFacetOpt('pivot', 'subject_type,subject_value_resolved');
		$this->solr->setFacetOpt('sort', 'subject_value_resolved');
		$this->solr->setFacetOpt('limit', '25000');
		$content = $this->solr->executeSearch();

		//if still no result is found, do a fuzzy search, store the old search term and search again
		if($this->solr->getNumFound()==0){
			if (!isset($filters['q'])) $filters['q'] = '';
			$new_search_term_array = explode(' ', $filters['q']);
			$new_search_term='';
			foreach($new_search_term_array as $c ){
				$new_search_term .= $c.'~0.7 ';
			}
			// $new_search_term = $data['search_term'].'~0.7';
			$this->solr->setOpt('q', 'fulltext:('.$new_search_term.') OR simplified_title:('.iconv('UTF-8', 'ASCII//TRANSLIT', $new_search_term).')');
			$this->solr->executeSearch();
		}

		$facets = $this->solr->getFacet();
		$facet_pivots = $facets->{'facet_pivot'}->{'subject_type,subject_value_resolved'};
		//echo json_encode($facet_pivots);
		$result = array();
		$result[$type] = array();
		
		foreach($facet_pivots as $p){
			if($p->{'value'}==$type){
				$result[$type] = array('count'=>$p->{'count'}, 'list'=>array());
				foreach($p->{'pivot'} as $pivot){
					array_push($result[$type]['list'], array('value'=>$pivot->{'value'}, 'count'=>$pivot->{'count'}));
				}
				$result[$type]['size'] = sizeof($result[$type]['list']);
				// echo json_encode($p->{'pivot'});
			}
		}
		return $result[$type];
	}

	function getsubjectfacet(){
		$filters = $this->input->post('filters');
		$data['subjectType'] = $this->input->post('subjectType');
		$this->load->view('subjectfacet', $data);
	}

	function getTopLevel(){
		$this->load->library('vocab');
		$filters = $this->input->post('filters');
		$fuzzy = $this->input->post('fuzzy');
		$fuzzy = ($fuzzy==='false') ? false : true;
		echo json_encode($this->vocab->getTopLevel('anzsrc-for', $filters, $fuzzy));
	}
}