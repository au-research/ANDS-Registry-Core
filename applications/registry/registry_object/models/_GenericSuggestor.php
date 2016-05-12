<?php
class _GenericSuggestor extends CI_Model {

	public $ro;
    public $index;
    public $ro_class;
    public $ro_key;
	
	public function set_ro($ro) {
		$this->ro = $ro;
	}

    public function set_ro_class($ro_class) {
        $this->ro_class = $ro_class;
    }

    public function set_ro_key($ro_key) {
        $this->ro_key = $ro_key;
    }

    public function set_index($index) {
        $this->index = $index;
    }

	public function suggest() {}

	function __construct() {
		parent::__construct();
		set_exception_handler('json_exception_handler');
		$this->ro = null;
        $this->ro_class = null;
        $this->ro_key = null;
        $this->index = null;
	}
}