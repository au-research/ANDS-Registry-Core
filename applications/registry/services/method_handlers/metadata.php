<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class MetadataMethod extends MethodHandler
{
	
	private $default_params = array(
		'q' => '*:*',
		'fl' => 'id,key,slug,display_title,class',
        'wt' => 'json',
        'indent' => 'on',
        'rows' => 20
    );
	
	
	//var $params, $options, $formatter; 
   function handle()
   {
   		// Get and handle a comma-seperated list of valid params which we will forward to the indexer
   		$permitted_forwarding_params = explode(',',$this->options['valid_solr_params']);

		// By default, disable row results in facet mode
		if (isset($this->params['facet']) && !isset($this->params['rows']))
		{
			$this->params['rows'] = 0;
		}

   		$forwarded_params = array_intersect_key(array_flip($permitted_forwarding_params), $this->params);
		
		$fields = array();
		foreach ($forwarded_params AS $param_name => $_)
		{
			$fields[str_replace("facet_","facet.",$param_name)] = $this->params[$param_name];
		}

		if (isset($this->params['debugAttributes']))
		{
			unset($this->default_params['fl']);
		}

		$fields = array_merge($this->default_params, $fields);
		
		$CI =& get_instance();
		$CI->load->library('solr');

		foreach($fields AS $key => $field)
		{
			$CI->solr->setOpt($key, $field);
		}

		$result = $CI->solr->executeSearch(true);
		
		if (!isset($this->params['debugQuery']))
		{	
			if (isset($result['response']))
			{
				$output = $result['response'];
			
				// Special case for the jswidget (which wants to know which internal reference ID the response maps to)
				if (isset($this->params['int_ref_id']))
				{
					$output['params'] = $result['responseHeader']['params'];
				}

				if (isset($result['facet_counts']))
				{
					$output['facet_counts'] = $result['facet_counts'];
				}
			}
			else
			{
				throw new Exception ("Error: Query interface did not return a response. Check your parameters. ");
			}
		}
		else
		{
			$output = $result;
		}
		
		// Bubble back the output status
		return $this->formatter->display($output);
   }
   
}