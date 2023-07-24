<?php

abstract class ExtensionBase {

	protected $_CI; 	// an internal reference to the CodeIgniter Engine 
	protected $db; 	// another internal reference to save typing!
	protected $ro; 	// a pointer to the parent registry object
	protected $id;
	
	function __construct($ro_pointer)
	{
		$this->ro = $ro_pointer;
		$this->_CI = &get_instance();
		$this->db = &$this->_CI->db;
		$this->id = &$this->ro->id;
	}
	
}
	