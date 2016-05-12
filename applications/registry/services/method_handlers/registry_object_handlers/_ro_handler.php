<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_objects.php');

class ROHandler extends Registry_objectsMethod {

    //overwrite me
	public function handle() {}

	function __construct($resource) {
		foreach($resource as $key=>$res) {
			$this->{$key} = $res;
		}
	}
}