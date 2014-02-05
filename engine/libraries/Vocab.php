<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Vocab resolving using sissvoc to use globally
 * @author : <leo.monus@ands.org.au>
 */
class Vocab {

	private $CI;
	private $resolvingServices;
    private $resolvedArray;

	/**
	 * Construction of this class
	 */
	function __construct(){
        $this->CI =& get_instance();
		$this->init();
    }

    /**
     * Initialize the solr class ready for call
     * @return [type] [description]
     */
    function init(){
        $this->resolvingServices = $this->CI->config->item('vocab_resolving_services');
    	$this->resolvedArray = array();
    	return true;
    }

    function resolveLabel($label, $vocabType)
    {
        if ($label)
        {
            if (isset($this->resolvingServices[$vocabType]['uriprefix']))
            {
                $vocab_config =  $this->resolvingServices[$vocabType];
            }
            else
            {
                throw new Exception("Unrecognised vocabulary: " . $vocabType);
            }
            $content = $this->post($this->constructUriString('label', $vocab_config, $label));
            if ($content)
            {
                // Did the vocab service resolve this label to a URI?
                $service_response = json_decode($content,true);
                if (isset($service_response['result']['items'][0]))
                {
                    $subject = array();
                    $subject['value'] = $service_response['result']['items'][0]['prefLabel']['_value'];
                    $subject['about'] = $service_response['result']['items'][0]['_about'];
                    $subject['notation'] = $service_response['result']['items'][0]['notation'];
                    return $subject;
                }
            }
        }
        
        return false;
    }

	function resolveSubject($term, $vocabType){
		
        if($vocabType != '' && is_array($this->resolvingServices) && array_key_exists($vocabType, $this->resolvingServices))
        {
            $resolvingService = $this->resolvingServices[$vocabType]['resolvingService'];
            $uriprefix = $this->resolvingServices[$vocabType]['uriprefix'];

            if(isset($this->resolvedArray[$uriprefix][$term]))
            {
                return $this->resolvedArray[$uriprefix][$term];
            }
            else
            {
                $content = $this->post($this->constructResorceUriString($resolvingService, $uriprefix, $term));
    		    $json = json_decode($content, false);
        		if($json){
        			$this->result = $json;
                    
                    $subject['uriprefix'] = $uriprefix;
                    $subject['notation'] = $term;
                    $subject['value'] = $json->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
                    $subject['about'] = $json->{'result'}->{'primaryTopic'}->{'_about'};
                    $this->resolvedArray[$uriprefix][$term] = $subject;
                    $this->resolvedArray[$uriprefix][$term]['broaderTerms'] = array();
                    $this->setBroaderSubjects($resolvingService, $uriprefix, $term, $vocabType);
        			return  $subject;
        		}else{
        			$subject['uriprefix'] = $uriprefix;
                    $subject['notation'] = $term;
                    $subject['value'] = $term;
                    $subject['about'] = '';
                    $this->resolvedArray[$uriprefix][$term] = $subject;
                    return $subject;
        		}
            }
        }
        elseif(isset($this->resolvedArray['non-resolvable'][$term]))
        {
            return $this->resolvedArray['non-resolvable'][$term];
        }
        else
        {
            $subject['uriprefix'] = 'non-resolvable';
            $subject['notation'] = $term;
            $subject['value'] = $term;
            $subject['about'] = '';
            $this->resolvedArray['non-resolvable'][$term] = $subject;
            return $subject;
        }
	}

    function post($queryStr){
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL,$queryStr);//post to SOLR
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
        $content = curl_exec($ch);//execute the curl
        curl_close($ch);//close the curl
        return $content;
    }

    function constructUriString($type, $vocab, $term){
        //$type can be resource or concept
        if($type=='resource'){
            $resourceQueryComp = 'resource.json?uri=';
        }else if($type=='broader'){
            $resourceQueryComp = 'concepts/allBroader.json?uri=';
        }else if($type=='label'){
            return $resourceQueryComp = $vocab['resolvingService']. 'concepts.json?anylabel=' . rawurlencode($term);
        }
        return $vocab['resolvingService'].$resourceQueryComp.$vocab['uriprefix'].$term;
    }

    function constructResorceUriString($resolvingService, $uriprefix, $term){
        $resourceQueryComp = 'resource.json?uri=';
        $uri = $resolvingService.$resourceQueryComp.urlencode($uriprefix.$term);
        return $uri;
    }

    function constructBroaderUriString($resolvingService, $uriprefix, $term){
        $broaderQueryComp = 'concepts/allBroader.json?uri=';
        $uri = $resolvingService.$broaderQueryComp.urlencode($uriprefix.$term);
        return $uri;
    }


    function setBroaderSubjects($resolvingService, $uriprefix, $term, $vocabType)
    {
        $json = false;
        if(is_array($this->resolvingServices))
        {
            $content = $this->post($this->constructBroaderUriString($resolvingService, $uriprefix, $term));
            $json = json_decode($content, false);
        }      
        if($json){
            $this->result = $json;
            foreach($json->{'result'}->{'items'} as $item)
            {               
                if(isset($item->{'broader'}))
                {
                    
                    $notation = $item->{'broader'}->{'notation'};
                    $subject['notation'] = $notation;
                    $subject['uriprefix'] = $uriprefix;
                    $subject['value'] = $item->{'broader'}->{'prefLabel'}->{'_value'};
                    $subject['about'] = $item->{'broader'}->{'_about'};
                    $this->resolvedArray[$uriprefix][$term]['broaderTerms'][] = $notation;
                    if(!isset($this->resolvedArray[$uriprefix][$notation]))
                    {
                        $this->resolvedArray[$uriprefix][$notation] = $subject;
                        $this->resolvedArray[$uriprefix][$notation]['broaderTerms'] = array();
                    }
                }
                if(isset($item->{'notation'}))
                {
                    $notation = $item->{'notation'};
                    $subject['notation'] = $notation;
                    $subject['uriprefix'] = $uriprefix;
                    $subject['value'] = $item->{'prefLabel'}->{'_value'};
                    $subject['about'] = $item->{'_about'};
                    $this->resolvedArray[$uriprefix][$term]['broaderTerms'][] = $notation;
                    if(!isset($this->resolvedArray[$uriprefix][$notation]))
                    {
                        $this->resolvedArray[$uriprefix][$notation] = $subject;
                        $this->resolvedArray[$uriprefix][$notation]['broaderTerms'] = array();
                    }
                } 
                          
            }
        }
    }

    function getBroaderSubjects($uriprefix, $term)
    {
        $result = array();
        if(is_array($this->resolvingServices) && isset($this->resolvedArray[$uriprefix][$term]) && isset($this->resolvedArray[$uriprefix][$term]['broaderTerms']))
        {
            $broaderTerms = $this->resolvedArray[$uriprefix][$term]['broaderTerms'];
            foreach($broaderTerms as $broaderTerm)
            {
                if(isset($this->resolvedArray[$uriprefix][$broaderTerm]))
                {
                    $broader = $this->resolvedArray[$uriprefix][$broaderTerm];
                    $result[$broaderTerm] = $this->resolvedArray[$uriprefix][$broaderTerm];
                }
            }
        }
        return $result;
    }

    function getResource($vocab_uri){
        $content = '';
        if(is_array($this->resolvingServices))
        {
            $curl_uri = $vocab_uri['resolvingService'].'resource.json?uri='.$vocab_uri['uriprefix'];
            // echo $curl_uri;
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL,$curl_uri);//post to SOLR
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
            $content = curl_exec($ch);//execute the curl
            //echo 'json received+<pre>'.$content.'</pre>';
            curl_close($ch);//close the curl
        }
        return $content;
    }

    function getNumCollections($uri,$filters, $fuzzy = false){
        $CI =& get_instance();
        $CI->load->library('solr');

        // var_dump($filters);
        $CI->solr->init();
        if($filters){
             $CI->solr->setFilters($filters);
        }

        $CI->solr->setOpt('fq', '+subject_vocab_uri:("'.$uri.'")');

        // var_dumP($CI->solr->constructFieldString());
        $CI->solr->executeSearch();

        if($CI->solr->getNumFound() > 0) return $CI->solr->getNumFound();
        //if still no result is found, do a fuzzy search, store the old search term and search again
        if($CI->solr->getNumFound()==0 && isset($filters['q']) && $fuzzy){
            $new_search_term_array = explode(' ', escapeSolrValue($filters['q']));
            $new_search_term='';
            foreach($new_search_term_array as $c ){
                $new_search_term .= $c.'~0.7 ';
            }
            $CI->solr->setOpt('q', 'fulltext:('.$new_search_term.') OR simplified_title:('.iconv('UTF-8', 'ASCII//TRANSLIT', $new_search_term).')');
            // $CI->solr->addQueryCondition('AND subject_vocab_uri:("'.$uri.'")');
            $CI->solr->executeSearch();
            if($CI->solr->getNumFound() > 0){
                $data['fuzzy_result'] = true;
            }
        }

    
        return $CI->solr->getNumFound();
     //    return $CI->solr->constructFieldString();
    }


    //RDA usage
    function getTopLevel($vocab, $filters, $fuzzy = false){
        $tree = array();
        if(is_array($this->resolvingServices))
        {
            header('Cache-Control: no-cache, must-revalidate');
            header('Content-type: application/json');
            $content = $this->post($this->constructUriString('resource', $this->resolvingServices[$vocab], ''));

            if($json = json_decode($content, false)){
                foreach($json->{'result'}->{'primaryTopic'}->{'hasTopConcept'} as $concept){
                    $concept_uri = $concept->{'_about'};
                    $uri['uriprefix']=$concept->{'_about'};
                    $uri['resolvingService']=$this->resolvingServices[$vocab]['resolvingService'];
                    $resolved_concept = json_decode($this->getResource($uri));
                    $notation = $resolved_concept->{'result'}->{'primaryTopic'}->{'notation'};
                    $c['notation'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'notation'};
                    $c['prefLabel'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
                    $c['uri'] = $resolved_concept->{'result'}->{'primaryTopic'}->{'_about'};
                    $c['collectionNum'] = $this->getNumCollections($c['uri'],$filters, $fuzzy);
                    if($c['collectionNum'] > 0){
                        $tree['topConcepts'][] = $c;
                    }
                }
            }
           
            if(isset($tree['topConcepts']) && is_array($tree['topConcepts']))
            {
                $sort = array();
                if(isset($filters['facetsort']) && $filters['facetsort']=='alpha'){ 
                    foreach((array)$tree['topConcepts'] as $key=>$c){
                        $sort[$key] = $c['prefLabel'];
                    }
                    array_multisort($sort, SORT_ASC, $tree['topConcepts']);
                }else{
                    foreach((array)$tree['topConcepts'] as $key=>$c){
                        $sort[$key] = $c['collectionNum'];
                    }
                    array_multisort($sort, SORT_DESC, $tree['topConcepts']);
                }
            }
        }
        return $tree;
    }

    function getConceptDetail($vocab, $url){
        $content = '';
        if(is_array($this->resolvingServices))
        {
            $vocab_uri['resolvingService'] = $this->resolvingServices[$vocab]['resolvingService'];
            $vocab_uri['uriprefix'] = $url;
            $content = $this->getResource($vocab_uri);
        }
        return $content;
    }

}