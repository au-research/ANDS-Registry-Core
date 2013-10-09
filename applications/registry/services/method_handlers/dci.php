<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class DCIMethod extends MethodHandler
{
	private $default_params = array(
		'q' => '*:* +class:("collection")',
		'fl' => 'id',
        'wt' => 'json',
        'rows' => 200
    );
	
	//var $params, $options, $formatter; 
   function handle()
   {
   		$output=array();

   			// Get and handle a comma-seperated list of valid params which we will forward to the indexer
   		$permitted_forwarding_params = explode(',',$this->options['valid_solr_params']);
   		$forwarded_params = array_intersect_key(array_flip($permitted_forwarding_params), $this->params);
		
		$fields = array();
		foreach ($forwarded_params AS $param_name => $_)
		{
			$fields[$param_name] = $this->params[$param_name];
		}

		$fields = array_merge($this->default_params, $fields);
		
		$CI =& get_instance();
		$CI->load->library('solr');

		foreach($fields AS $key => $field)
		{
			$CI->solr->setOpt($key, $field);
		}
		
		// Only pull back collections!
		$CI->solr->setOpt('fq','class:(collection)');

		// Get back a list of IDs for matching registry objects
		$result = $CI->solr->executeSearch(true);
	
		$rifcsOutput = array();
		if (isset($result['response']['docs']) && is_array($result['response']['docs']))
		{
			foreach ($result['response']['docs'] AS $result)
			{
				$CI->load->model('registry_object/registry_objects','ro');
				$registryObject = $CI->ro->getByID($result['id']);
				if ($registryObject && $registryObject->class == 'collection')
				{
					$rifcsOutput[] .= $registryObject->transformToDCI();
				}
			}
		}

		// Bubble back the output status
		return $this->formatter->display($rifcsOutput);
   }
}