<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class GRANTSMethod extends MethodHandler
{
	//var $params, $options, $formatter; 
   function handle()
   {
   		$output=array();
		$response = array();
   			// Get and handle a comma-seperated list of valid params which we will forward to the indexer
   		$permitted_forwarding_params = explode(',',$this->options['valid_params']);
   		$forwarded_params = array_intersect_key(array_flip($permitted_forwarding_params), $this->params);
		$CI =& get_instance();
		$CI->load->library('solr');
		$gotQuery = false;
		foreach ($forwarded_params AS $param_name => $_)
		{
			//display_title,researcher,year,institution
			if($param_name == 'title' && $this->params[$param_name] != ''){
				$gotQuery =true;
				$CI->solr->setOpt('fq','+display_title:('.$this->params[$param_name].')');
			}
			if($param_name == 'year' && $this->params[$param_name] != '')
			{
				$gotQuery =true;
				$CI->solr->setOpt('fq',' earliest_year:'.$this->params[$param_name].' latest_year:'.$this->params[$param_name]);
			}
			if($param_name == 'institution' && $this->params[$param_name] != '')
			{
				$gotQuery =true;
				$CI->solr->setOpt('fq','+related_object_display_title:'.$this->params[$param_name].'');
				$CI->solr->setOpt('fq','+related_object_relation:"isManagedBy"');				
			}
			if(($param_name == 'person' || $param_name == 'principalInvestigator') && $this->params[$param_name] != '')
			{
				$gotQuery =true;
				$CI->solr->setOpt('fq','+related_object_display_title:'.$this->params[$param_name]);
				$CI->solr->setOpt('fq','+related_object_class:"party"');
				
			}
		}
		

		if($gotQuery)
		{		
			$CI->solr->setOpt('fq','+class:"activity"');
			$CI->solr->setOpt('fq','+group:"National Health and Medical Research Council"');
			// Get back a list of IDs for matching registry objects
			$result = $CI->solr->executeSearch(true);
			//$result = $CI->solr->getResult();
			$response['numFound'] = $result['response']['numFound'];

			//$response['query'] = $CI->solr->constructFieldString();
			//return $this->formatter->display($response);
			//exit();
			$recordData = array();
			if (isset($result['response']['docs']) && is_array($result['response']['docs']))
			{			
				foreach ($result['response']['docs'] AS $r)
				{
						$relationships = array();
						
						if(isset($r['related_object_display_title']))
						{
							$relationships = $this->processRelated($r['related_object_display_title'],$r['related_object_relation']);
						}
											
						$recordData[] = array('key' => $r['key'], 
							'slug' => $r['slug'], 
							'title' =>  $r['display_title'], 
							'description' =>  $r['description'],
							'relations' => $relationships);
				}
			}
			$response['recordData'] = $recordData;
		}
		// Bubble back the output status
		return $this->formatter->display($response);
   }

   function processRelated($titles,$relation)
   {
		$relatiships = array();
		for($i = 0 ; $i < sizeof($relation) ; $i++)
		{
			if(isset($relatiships[$relation[$i]]))
			{
				if(is_array($relatiships[$relation[$i]]))
				{
					$relatiships[$relation[$i]][] = $titles[$i];
				}
				else{
					$firstTitle = $relatiships[$relation[$i]];
					$relatiships[$relation[$i]] = array();
					$relatiships[$relation[$i]][] = $firstTitle;
					$relatiships[$relation[$i]][] = $titles[$i];
				}

			}
			else{
				$relatiships[$relation[$i]] = $titles[$i];
			}
		}
		return $relatiships;

   }
}