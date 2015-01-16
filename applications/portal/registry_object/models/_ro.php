<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Registry Object model for a single registry object
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class _ro {

	//object properties are all located in the same array
	public $prop;


	function __construct($id, $populate=array('core')) {
		//populate the property as soon as the object is constructed
		$this->init($id, $populate);
	}

	/**
	 * Initialize a registry object
	 * @param  int $id       registry object id
	 * @param  array  $populate a list of attributes to populate, default to just core
	 * @return void           
	 */
	function init($id, $populate = array('core')) {
		$this->prop = array(
			'id' => $id
		);
		$this->fetch($populate);
	}

	/**
	 * Magic function to get an attribute, returns property within the $prop array
	 * @param  string $property property name
	 * @return property result           
	 */
	public function __get($property) {
		if(isset($this->prop[$property])) {
			return $this->prop[$property];
		} else return false;
	}

	/**
	 * Magic function to set an attribute
	 * @param string $property property name
	 * @param string $value    property value
	 */
	public function __set($property, $value) {
		$this->prop[$property] = $value;
	}

	/**
	 * Construct the API URL based on the amount of data required
	 * @todo   add support for setting a special API key from the configuration
	 * @param  array  $params a list of parameter to query
	 * @return string $url         
	 */
	public function construct_api_url($params = array('core')) {
		$url = base_url().'registry/services/api/registry_objects/'.$this->id.'/';
		foreach($params as $par) {
			$url.=$par.'-';
		}
		return $url;
	}

	/**
	 * Fetch data from the Registry API
	 * @param  array  $params list of parameters to fetch
	 * @todo  ERROR HANDLING
	 * @return void         
	 */
	public function fetch($params = array('core')) {
		
		//get the URL
		$url = $this->construct_api_url($params);
		$this->prop['api_url'] = $url;
		
		//Fetch the data and populate as per the result
  		$content = @file_get_contents($url);
		$content = json_decode($content, true);

		if ($content['status']=='success') {
			foreach($params as $par) {
				if(isset($content['message'][$par])) {
					foreach($content['message'][$par] as $attr=>$val) {
						$this->prop[$par][$attr] = $val;
					}
				}
			}
		}
	}

	//deprecated?
	public function populate($par) {
		$this->fetch(array($par));
	}
}