<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * Vocab resolving using sissvoc to use globally
 * @author : <leo.monus@ands.org.au>
 */
class Vocab {

	private $CI;
	private $resolvingServices;
    private $resolvedArray;
    private $facets;

	/**
	 * Construction of this class
	 */
	function __construct(){
        $this->CI =& get_instance();
        $this->CI->load->driver('cache');
		$this->init();
    }

    /**
     * Initialize the solr class ready for call
     * @return [type] [description]
     */
    function init(){
        $this->resolvingServices = \ANDS\Util\config::get('vocab.vocab_resolving_services');
    	$this->resolvedArray = array();
    	return $this;
    }

    function resolveLabel($label, $vocabType)
    {
        if ($label)
        {
            if (isset($this->resolvingServices[$vocabType]['resolvingService']))
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
                    if (isset($service_response['result']['items'][0]['notation'])) {
                        $subject['notation'] = $service_response['result']['items'][0]['notation'];
                    } else {
                        // No, don't set it. Callers seem to test for
                        // the value being missing (unset).
                        // $subject['notation'] = "";
                    }
                    return $subject;
                }
            }
        }
        
        return false;
    }

    // Precondition: $vocabType has a resolvingService entry.
    // (All existing calls seem to satisfy the precondition.)
    function anyContains($term, $vocabType){
        $result = array();
        if($term){
            $curl_uri = $this->resolvingServices[$vocabType]['resolvingService'].'concept.json?anycontains='.$term;
            // echo $curl_uri;
            $ch = curl_init();
            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL,$curl_uri);//post to SOLR
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//return to variable
            $content = curl_exec($ch);//execute the curl
            //echo 'json received+<pre>'.$content.'</pre>';
            curl_close($ch);//close the curl

            $json = json_decode($content, true);

            foreach($json['result']['items'] as $i){
                if(isset($i['prefLabel'])){
                    array_push($result, $i['prefLabel']['_value']);
                }
            }
        }
        return $result;
    }

    // NB here: $term is the concept's notation
	function resolveSubject($term, $vocabType){
		$term = trim($term);
        $vocabType = trim($vocabType);
        if($vocabType != '' && is_array($this->resolvingServices)
           && array_key_exists($vocabType, $this->resolvingServices)
           && isset($this->resolvingServices[$vocabType]['resolvingService']))
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
                    //don't cache errored unresolvable in case it was just a glitch try again next time
                    //$this->resolvedArray[$uriprefix][$term] = $subject;
                    return $subject;
                }
            }
        }elseif(isset($this->resolvedArray['non-resolvable'][$term]))
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

    // TODO: Future work is to support getting broader subjects
    // starting with a concept's IRI, rather than notation.
    // So, this is a copy/paste of resolveSubject(), with
    // $term changed to $uri. However, other changes may be needed.
    // When this function is needed, uncomment it and complete
    // it. See also getBroaderSubjectsByUri().
    // NB here: $uri is the concept's URI (a.k.a. IRI)
    // FIXME This is not finished. Don't use!
	// function resolveSubjectByUri($uri, $vocabType){
		
    //     if($vocabType != '' && is_array($this->resolvingServices) && array_key_exists($vocabType, $this->resolvingServices) && isset($this->resolvingServices[$vocabType]['resolvingService']))
    //     {
    //         $resolvingService = $this->resolvingServices[$vocabType]['resolvingService'];
    //         $uriprefix = $this->resolvingServices[$vocabType]['uriprefix'];

    //         if(isset($this->resolvedArray[$uriprefix][$uri]))
    //         {
    //             return $this->resolvedArray[$uriprefix][$uri];
    //         }
    //         else
    //         {
    //             // Use a trick: supply empty string as the second parameter in order
    //             $content = $this->post($this->constructResorceUriString($resolvingService, '', $uri));
    // 		    $json = json_decode($content, false);
    //     		if($json){
    //     			$this->result = $json;
                    
    //                 $subject['uriprefix'] = $uriprefix;
    //                 $subject['notation'] = '';
    //                 $subject['value'] = $json->{'result'}->{'primaryTopic'}->{'prefLabel'}->{'_value'};
    //                 $subject['about'] = $json->{'result'}->{'primaryTopic'}->{'_about'};
    //                 $this->resolvedArray[$uriprefix][$uri] = $subject;
    //                 $this->resolvedArray[$uriprefix][$uri]['broaderTerms'] = array();
    //                 $this->setBroaderSubjects($resolvingService, $uriprefix, $uri, $vocabType);
    //     			return  $subject;
    //     		}else{
    //     			$subject['uriprefix'] = $uriprefix;
    //                 $subject['notation'] = $uri;
    //                 $subject['value'] = $uri;
    //                 $subject['about'] = '';
    //                 $this->resolvedArray[$uriprefix][$uri] = $subject;
    //                 return $subject;
    //     		}
    //         }
    //     }
    //     elseif(isset($this->resolvedArray['non-resolvable'][$uri]))
    //     {
    //         return $this->resolvedArray['non-resolvable'][$uri];
    //     }
    //     else
    //     {
    //         $subject['uriprefix'] = 'non-resolvable';
    //         $subject['notation'] = $uri;
    //         $subject['value'] = $uri;
    //         $subject['about'] = '';
    //         $this->resolvedArray['non-resolvable'][$uri] = $subject;
    //         return $subject;
    //     }
	// }


    function post($queryStr){

        $cacheId = $this->getCacheID($queryStr);

        // return cached version
        if ($cached = $this->CI->cache->file->get($cacheId)) {
            return $cached;
        }

        //execute curl
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$queryStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // only log if the response is 200
        if ($http_code === 200) {
            $this->CI->cache->file->save($cacheId, $content, 36000);
        } //else {
         //   ulog("vocab response error: " . $http_code . " (" . $queryStr .")", "error", "error");
        //}

        return $content;
    }

    public function getCacheID($url)
    {
        return 'vocabLib-'.md5($url);
    }

    function constructUriString($type, $vocab, $term){
        //$type can be resource or concept
        if($type=='resource'){
            $resourceQueryComp = 'resource.json?uri=';
        }else if($type=='broader'){
            $resourceQueryComp = 'concept/allBroader.json?uri=';
        }else if($type=='label'){
            return $resourceQueryComp = $vocab['resolvingService']. 'concept.json?anylabel=' . rawurlencode($term);
        }
        return $vocab['resolvingService'].$resourceQueryComp.$vocab['uriprefix'].$term;
    }

    function constructResorceUriString($resolvingService, $uriprefix, $term){
        $resourceQueryComp = 'resource.json?uri=';
        $uri = $resolvingService.$resourceQueryComp.urlencode($uriprefix.$term);
        return $uri;
    }

    function constructBroaderUriString($resolvingService, $uriprefix, $term){
        $broaderQueryComp = 'concept/allBroader.json?uri=';
        $uri = $resolvingService.$broaderQueryComp.urlencode($uriprefix.$term);
        return $uri;
    }


    function setBroaderSubjects($resolvingService, $uriprefix, $term, $vocabType)
    {
        $json = false;
        if(is_array($this->resolvingServices))
        {
            // var_dump($this->constructBroaderUriString($resolvingService, $uriprefix, $term));
            $content = $this->post($this->constructBroaderUriString($resolvingService, $uriprefix, $term));
            $json = json_decode($content, false);
        }      
        if($json){
            $this->result = $json;
            foreach($json->{'result'}->{'items'} as $item) {
                $notation = $item->{'notation'};
                $subject['notation'] = $notation;
                $subject['uriprefix'] = $uriprefix;
                $subject['value'] = $item->{'prefLabel'}->{'_value'};
                $subject['about'] = $item->{'_about'};
                $this->resolvedArray[$uriprefix][$term]['broaderTerms'][] = $notation;
                if(!isset($this->resolvedArray[$uriprefix][$notation])) {
                    $this->resolvedArray[$uriprefix][$notation] = $subject;
                    $this->resolvedArray[$uriprefix][$notation]['broaderTerms'] = array();
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

    // TODO: Future work is to support getting broader subjects
    // starting with a concept's IRI, rather than notation.
    // So, this is a copy/paste of getBroaderSubjects(), with
    // $term changed to $uri. However, other changes may be needed.
    // When this function is needed, uncomment it and complete
    // it. See also resolveSubjectByUri().
    // function getBroaderSubjectsByUri($uriprefix, $uri)
    // {
    //     $result = array();
    //     if(is_array($this->resolvingServices) && isset($this->resolvedArray[$uriprefix][$uri]) && isset($this->resolvedArray[$uriprefix][$uri]['broaderTerms']))
    //     {
    //         $broaderTerms = $this->resolvedArray[$uriprefix][$uri]['broaderTerms'];
    //         foreach($broaderTerms as $broaderTerm)
    //         {
    //             if(isset($this->resolvedArray[$uriprefix][$broaderTerm]))
    //             {
    //                 $broader = $this->resolvedArray[$uriprefix][$broaderTerm];
    //                 $result[$broaderTerm] = $this->resolvedArray[$uriprefix][$broaderTerm];
    //             }
    //         }
    //     }
    //     return $result;
    // }

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
        unset($filters['anzsrc-for']);
        $CI =& get_instance();
        $CI->load->library('solr');
        // ulog('trying to get count for '.$uri);
        if(!$this->facets) {
            //build the facet cache
            // ulog('building cache for '.$uri);
            $CI->solr->init();
            if($filters){
                $CI->solr->setFilters($filters);
            }
            if(!isset($filters['class'])) $CI->solr->setOpt('fq', '+class:(collection)');
            $CI->solr->setOpt('fl', 'id')->setOpt('rows', 0);
            $CI->solr->setFacetOpt('field', 'subject_vocab_uri')->setFacetOpt('limit', '-1');
            $result = $CI->solr->executeSearch(true);
            $solr_facets = $result['facet_counts']['facet_fields']['subject_vocab_uri'];
            $facets = array();
            for($i=0;$i<sizeof($solr_facets)-1;$i+=2) {
                $facets[$solr_facets[$i]] = $solr_facets[$i+1];
            }
            $this->facets = $facets;
            // ulog('built cache and got count for '.$uri.' from facet cache: '.$this->facets[$uri]);
            if(isset($this->facets[$uri])) {
               return $this->facets[$uri];
            } else return 0;
        } else {
            if (isset($this->facets[$uri])) {
                //get it from the facet cache, faster
                // ulog('got count for '.$uri.' from the facet cache: '.$this->facets[$uri]);
                return $this->facets[$uri];
            } else {
                // return 0;
                
                //it's not in the facet cache, have to do some thing
                $CI->solr->init();
                if($filters){
                    $CI->solr->setFilters($filters);
                }

                $CI->solr->setOpt('fq', '+subject_vocab_uri:("'.$uri.'")');

                //default to collection
                if(!isset($filters['class'])) $CI->solr->setOpt('fq', '+class:(collection)');

                // var_dumP($CI->solr->constructFieldString());
                $CI->solr->executeSearch();
                // ulog('count for '.$uri.' from search: '.$CI->solr->getNumFound());
                return $CI->solr->getNumFound();
            }
        }
        


        
     //    return $CI->solr->constructFieldString();
    }


    //RDA usage
    function getTopLevel($vocab, $filters, $fuzzy = false){
        $tree = array();
        if(is_array($this->resolvingServices))
        {
            // header('Cache-Control: no-cache, must-revalidate');
            // header('Content-type: application/json');
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
                    $c['has_narrower'] = (isset($resolved_concept->{'result'}->{'primaryTopic'}->{'narrower'}) && sizeof($resolved_concept->{'result'}->{'primaryTopic'}->{'narrower'}) > 0) ? true : false;
                    if(isset($c['collectionNum']) > 0){
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