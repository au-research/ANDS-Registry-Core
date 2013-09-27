<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class GRANTSMethod extends MethodHandler
{
	//var $params, $options, $formatter; 
   function handle()
   {
   		$output=array();

   			// Get and handle a comma-seperated list of valid params which we will forward to the indexer
   		$permitted_forwarding_params = explode(',',$this->options['valid_params']);
   		$forwarded_params = array_intersect_key(array_flip($permitted_forwarding_params), $this->params);
		
		$sq = '';
		foreach ($forwarded_params AS $param_name => $_)
		{
			//display_title,researcher,year,institution
			if($param_name == 'title')
				$sq .= '+display_title:('.$this->params[$param_name].')';
			if($param_name == 'year')
			{
				$sq .=' earliest_year:'.$this->params[$param_name];
				$sq .=' latest_year:'.$this->params[$param_name];
			}
		}
		//exit();
		$CI =& get_instance();
		$CI->load->library('solr');
		$CI->solr->setOpt('fq',$sq);
		$CI->solr->setOpt('fq','+class:"activity"');



		// Get back a list of IDs for matching registry objects
		$result = $CI->solr->executeSearch(true);
		// $result = $CI->solr->getResult();

		$response = array();

		$response['numFound'] = $result['response']['numFound'];
		$response['opts'] = $this->params;

		$recordData = array();
		if (isset($result['response']['docs']) && is_array($result['response']['docs']))
		{			
			foreach ($result['response']['docs'] AS $r)
			{
					$recordData[] = array('key' => $r['key'], 'title' =>  $r['display_title'], 'description' > 'title' =>  $r['description']);
			}
		}
		$response['recordData'] = $recordData;
		// Bubble back the output status
		return $this->formatter->display($response);
   }
}