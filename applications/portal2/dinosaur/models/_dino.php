<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class _dino {

	public $prop;

	function __construct($id) {
		$this->init($id);
	}

	function init($id) {
		$this->prop = array(
			'id' => $id,
			'name' => 'Tyranosaurus Rex',
			'suffix' => 'WRX',
			'type' => 'vegetarian'
		);
		
	}

	public function __get($property) {
		if(isset($this->prop[$property])) {
			return $this->prop[$property];
		}
	}

	public function __set($property, $value) {
		$this->prop[$property] = $value;
	}

	public function populate($par) {
		$this->fetch(array($par));
	}

	public function fetch($params = array('core')) {
		$url ='https://devl.ands.org.au/minh/registry/services/api/registry_objects/'.$this->id.'/';
		foreach($params as $par) {
			$url.=$par.'-';
		}
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

}