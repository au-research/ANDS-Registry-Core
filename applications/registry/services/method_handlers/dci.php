<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/_method_handler.php');

class DCIMethod extends MethodHandler
{
	private $default_params = array(
		'q' => '*:* +class:("collection")',
        'fl' => 'id,key,slug,title,class,type,data_source_id,group,created,status,subject_value_resolved,list_description,earliest_year,latest_year',
        'wt' => 'json',
        'rows' => 200
    );

    private $valid_methods = array(
        'core', 'dci'
    );

    public $ro = null;
    public $index = null;
    public $xml = null;
    public $overrideExportable = null;
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
                $CI->load->model('data_source/data_sources','ds');
                $this->ro = new _registry_object($result['id']);
                $this->populate_resource($result['id'], true);
                $rifcsOutput[] = $this->ro_handle('dci');
			}
		}
		// Bubble back the output status
		return $this->formatter->display($rifcsOutput);
   }

    function populate_resource($id, $overrideExportable = false) {

        //local SOLR index for fast searching
        $ci =& get_instance();
        $ci->load->library('solr');
        $ci->solr->clearOpt('fq');
        $ci->solr->setOpt('fq', '+id:'.$id);
        $this->overrideExportable = $overrideExportable;
        $result = $ci->solr->executeSearch(true);
        if(sizeof($result['response']['docs']) == 1) {
            $this->index = $result['response']['docs'][0];
        }
        //local XML resource
        $xml = $this->ro->getSimpleXML();
        $xml = addXMLDeclarationUTF8(($xml->registryObject ? $xml->registryObject->asXML() : $xml->asXML()));
        $xml = simplexml_load_string($xml);
        $xml = simplexml_load_string( addXMLDeclarationUTF8($xml->asXML()) );
        if ($xml) {
            $this->xml = $xml;
            $rifDom = new DOMDocument();
            $rifDom->loadXML($this->ro->getRif());
            $gXPath = new DOMXpath($rifDom);
            $gXPath->registerNamespace('ro', 'http://ands.org.au/standards/rif-cs/registryObjects');
            $this->gXPath = $gXPath;
        }
    }


    function ro_handle($handler) {
        require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/'.$handler.'.php');
        $handler = new $handler($this->get_resource());
        return $handler->handle();
    }

    function get_resource() {
        return array(
            'index' => $this->index, //escape &
            'xml' => $this->xml,
            'gXPath' => $this->gXPath,
            'ro' => $this->ro,
            'params' => $this->params,
            'default_params' => $this->default_params,
            'overrideExportable' => $this->overrideExportable
        );
    }

}