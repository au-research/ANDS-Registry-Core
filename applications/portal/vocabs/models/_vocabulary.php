<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Basse Vocabulary model for a single vocabulary object
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class _vocabulary {

	//object properties are all located in the same array
	public $prop;

	function __construct() {
		//populate the property as soon as the object is constructed
		$this->init();
	}

	/**
	 * Initialize a registry object
	 * @todo
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return void
	 */
	function init() {
		
	}

	/**
	 * Returns a flat array of indexable fields
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return array 
	 */
	public function indexable_json() {
		$json = array();
		$single_values = array('id', 'title', 'slug', 'licence');
		foreach ($single_values as $s) {
			$json[$s] = $this->prop[$s];
		}
		return $json;
	}


	/**
	 * Populate the prop array with an array of key=>value pair
	 * @param  array  $values $key=>$value pair
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return void
	 */
	public function populate($values = array()) {
		foreach ($values as $key=>$value) {
			$this->prop[$key] = $value;
		}
	}

	/**
	 * Magic function to get an attribute, returns property within the $prop array
	 * @param  string $property property name
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
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
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 */
	public function __set($property, $value) {
		$this->prop[$property] = $value;
	}

	/**
	 * Magic function to return the object as a JSON encoded string
	 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @return string
	 */
	public function __toString() {
		return json_encode($this->prop);
	}
}