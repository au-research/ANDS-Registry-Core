<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class NativeMethod extends MethodHandler
{
	private $default_params = array(
		'q' => '*:*',
		'fl' => 'id',
        'wt' => 'json',
        'rows' => 20
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

		// Get back a list of IDs for matching registry objects
		$solr_result = $CI->solr->executeSearch(true);

		$rifcsOutput = array();
		$CI->load->helper('crosswalks');
		$crosswalks = getCrossWalks();
		if (isset($solr_result['response']['docs']) && is_array($solr_result['response']['docs']))
		{
			foreach ($solr_result['response']['docs'] AS $result)
			{
			
				$CI->load->model('registry_object/registry_objects','ro');
				$registryObject = $CI->ro->getByID((int) $result['id']);

				if ($registryObject)
				{
					$nativeFormat = $registryObject->getNativeFormat();
					// Try and get the native version?
					if (in_array($nativeFormat, array(RIFCS_SCHEME, EXTRIF_SCHEME)))
					{
						$registryObjects[] = array(	
													"slug"=>$registryObject->slug,
													"scheme"=>$nativeFormat, 
													"data"=>wrapRegistryObjects($registryObject->getRif())
												);
					}
					else
					{
						// Check for a matching scheme from a crosswalk
						$matched = false;
						foreach ($crosswalks AS $cw)
						{
							if ($cw->metadataFormat() == $nativeFormat)
							{
								$matched = true;
								$registryObjects[] = array(	
															"slug"=>$registryObject->slug,
															"scheme"=>$nativeFormat, 
															"data"=>$cw->wrapNativeFormat($registryObject->getNativeFormatData())
														);
							}
						}

						// If no matching crosswalk wraped, then just spit out whatever we've got...
						if (!$matched)
						{
							$registryObjects[] = array(	
														"slug"=>$registryObject->slug,
														"scheme"=>$nativeFormat, 
														"data"=>$registryObject->getNativeFormatData()
													);
						}
					}
				}
			}
		}
		$rifcsOutput = array("numFound" => $solr_result['response']['numFound'], "start"=> $solr_result['response']['start'], "docs"=> &$registryObjects);
		// Bubble back the output status
		return $this->formatter->display($rifcsOutput);
   }
}