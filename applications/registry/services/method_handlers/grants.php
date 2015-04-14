<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class GRANTSMethod extends MethodHandler
{
	//var $params, $options, $formatter; 
   function handle()
   {


        $output=array();
		$response = array();
		$principalInvestigator = null;
		$institution = null;
   			// Get and handle a comma-seperated list of valid params which we will forward to the indexer
   		$permitted_forwarding_params = explode(',',$this->options['valid_params']);
   		$forwarded_params = array_intersect_key(array_flip($permitted_forwarding_params), $this->params);
		$CI =& get_instance();
		$CI->load->library('solr');
        $ro = $CI->load->model('registry_object/registry_objects');
		$gotQuery = false;

        $defaultGroups = '"National Health and Medical Research Council","Australian Research Council"';

		foreach ($forwarded_params AS $param_name => $_)
		{
            //Determine which groups we are searching against
            if($param_name == 'group' && $this->params[$param_name] != ''){
                $defaultGroups = '"'.$this->params[$param_name].'"';
            }

            $CI->solr->setOpt('fq','+group:('.$defaultGroups.')');
			//display_title,researcher,year,institution
			if($param_name == 'title' && $this->params[$param_name] != ''){
				$words = $this->getWords($this->params[$param_name]);
				foreach($words as $word)
				{
					$CI->solr->setOpt('fq','+title_search:('.$word.')');
					$gotQuery =true;
				}				
			}

            if($param_name == 'id' && $this->params[$param_name] != '')
            {
                $CI->solr->setOpt('fq','+identifier_value:*'.$this->params[$param_name].'*');
                $gotQuery =true;
            }
            if($param_name == 'description' && $this->params[$param_name] != '')
            {

               $words = $this->getWords($this->params[$param_name]);
               foreach($words as $word)
               {
                    $CI->solr->setOpt('fq','+description:('.$word.')');
                }
                $gotQuery =true;
            }
			if($param_name == 'institution' && $this->params[$param_name] != '')
			{
				$CI->solr->setOpt('fq','+related_party_multi_search:"'.$this->params[$param_name].'"');
				$gotQuery =true;

				//$CI->solr->setOpt('fq','+related_object_relation:"isManagedBy"');
			}
			if($param_name == 'person' && $this->params[$param_name] != '')
			{
				$words = $this->getWords($this->params[$param_name]);
				foreach($words as $word)
				{
					$CI->solr->setOpt('fq',' related_party_one_search:('.$word.') OR researchers:('.$word.')');
				}
				$gotQuery =true;

				//$CI->solr->setOpt('fq','+related_object_class:"party"');
			}
			if($param_name == 'principalInvestigator' && $this->params[$param_name] != '')
			{
				$words = $this->getWords($this->params[$param_name]);
				foreach($words as $word)
				{
					$CI->solr->setOpt('fq','+related_party_one_search:('.$word.')');
				}	

				$gotQuery =true;

				//$CI->solr->setOpt('fq','+related_object_relation:"isPrincipalInvestigatorOf"');

			}
			
		}
		

		if($gotQuery)
		{		
			$CI->solr->setOpt('fq','+class:"activity"');
			$CI->solr->setOpt('rows','999');

			// Get back a list of IDs for matching registry objects
			$result = $CI->solr->executeSearch(true);
			//$result = $CI->solr->getResult();
			$response['numFound'] = 0;

			//$response['query'] = $CI->solr->constructFieldString();
			//return $this->formatter->display($response);
			//exit();

			$recordData = array();
			if (isset($result['response']['docs']) && is_array($result['response']['docs']))
			{			
				foreach ($result['response']['docs'] AS $r)
				{

                    $relationships = array();
                    $identifiers = array();
					$canPass= true;
                    $r_o = $ro->getById($r['id']);
                    $related = $r_o->getRelatedObjectsByClassAndRelationshipType(array('party'), array());

    				if(isset($related))
					{
						$relationships = $this->processRelated($related);
					}

                    if(isset($r['identifier_value']))
                    {
                        $identifiers = $this->processIdentifiers($r['identifier_value'],$r['identifier_type']);
                    }
					// POST FILTERS
					if(isset($institution) && isset($relationships['isManagedBy']))
					{
						$canPass = false;
						if(is_array($relationships['isManagedBy']))
						{
							for($i = 0 ; $i < sizeof($relationships['isManagedBy']) ; $i++)
							{
								$words = $this->getWords($relationships['isManagedBy'][$i]);
								for($i = 0 ; $i < sizeof($institution) ; $i++)
								{
									if(!$canPass)
										$canPass = in_array($institution[$i], $words);
								}
							}
						}
						else
						{
							$words = $this->getWords($relationships['isManagedBy']);
							for($i = 0 ; $i < sizeof($institution) ; $i++)
							{
								if(!$canPass)
									$canPass = in_array($institution[$i], $words);
							}

						}
					}

					if(isset($principalInvestigator) && isset($relationships['isPrincipalInvestigatorOf']))
					{
						$canPass = false;
						if(is_array($relationships['isPrincipalInvestigatorOf']))
						{
							for($i = 0 ; $i < sizeof($relationships['isPrincipalInvestigatorOf']) ; $i++)
							{
								$words = $this->getWords($relationships['isPrincipalInvestigatorOf'][$i]);
								for($i = 0 ; $i < sizeof($principalInvestigator) ; $i++)
								{
									if(!$canPass)
										$canPass = in_array($principalInvestigator[$i], $words);
								}
							}
						}
						else
						{
							$words = $this->getWords($relationships['isPrincipalInvestigatorOf']);
							for($i = 0 ; $i < sizeof($principalInvestigator) ; $i++)
							{
								if(!$canPass)
									$canPass = in_array($principalInvestigator[$i], $words);
							}

						}
					}

					if($canPass)
					{
					$response['numFound'] += 1;
					$recordData[] = array('key' => $r['key'], 
						'slug' => $r['slug'], 
						'title' =>  $r['display_title'],
						'description' =>  $r['description'],
                        'identifiers' => $r['identifier_value'],
                        'identifier_type' =>$identifiers,
						'relations' => $relationships);
					}
				}
			}
			$response['recordData'] = $recordData;
		}
		// Bubble back the output status
       if(isset($_GET['callback']))
       {
            set_exception_handler('json_exception_handler');
            header('Cache-Control: no-cache, must-revalidate');
            header('Content-type: application/json');
            $callback = (isset($_GET['callback'])? $_GET['callback']: '?');
		    return $this->JSONP($callback,json_encode(array("status"=>"success", "message"=>$response)));
       }else{
            return $this->formatter->display($response);
       }
   }

    private function JSONP($callback, $r){
        echo ($callback) . '(' . $r . ')';
    }

   function processRelated($related)
   {
		$relatiships = array();
		for($i = 0 ; $i < sizeof($related) ; $i++)
		{
			if(isset($related[$i]['relation_type'])&&$related[$i]['status']=='PUBLISHED')
			{
				if(isset($relatiships[$related[$i]['relation_type']]))
				{
					$relatiships[$related[$i]['relation_type']][] = $related[$i]['title'];
				}
				else{
					$firstTitle = $related[$i]['title'];
					$relatiships[$related[$i]['relation_type']] = array();
					$relatiships[$related[$i]['relation_type']][] = $firstTitle;
				}

			}

		}

        foreach($relatiships as $key=>$relationship){
           if(count($relationship)==1){
               $relatiships[$key] = $relationship[0];
           }
        }
		return $relatiships;

   }
    function processIdentifiers($value,$type)
    {
        $identifiers = array();
        for($i = 0 ; $i < sizeof($type) ; $i++)
        {
            if(isset($identifiers[$type[$i]]))
            {
                if(is_array($identifiers[$type[$i]]))
                {
                    $identifiers[$type[$i]][] = $value[$i];
                }


            }
            else{
                $identifiers[$type[$i]] = $value[$i];
            }
        }
        return $identifiers;

    }
   function getWords($string)
   {
   		$invalid_characters = array("$", "%", "#", "<", ">", "|", '"', "'", "(", ")");
   		$stopWords = array("a", "an", "and", "are", "as", "at", "be", "but", "by", "for", "if", "in", "into", "is", "it", "no", "not", "of", "on", "or", "s", "such", "t", "that", "the", "their", "then", "there", "these", "they", "this", "to", "was", "will", "with");
		$string = str_replace($invalid_characters, "", strtolower($string));
		$words = explode(" " , $string);
		$words = array_diff($words,$stopWords);
		return $words;
   }
}