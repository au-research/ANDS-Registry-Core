<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Example_extension extends ExtensionBase
{
	// Define your properties and methods here
	
	// You can reference other extensions from $this->ro->
	// i.e. $this->ro->updateTitles()
	
	// You can make database calls using $this->db->  (see: ActiveRecord)
	
	// You can access the rest of CodeIgniter through
	// $this->_CI->  
	
	// From outside the registry object, this can simply by called by
	// $ro->exampleMethod();
	function exampleMethod()
	{
		$this->ro->exampleMethodCalled = TRUE;
		$this->ro->save();
	}
	
	/**
	 * @ignore
	 * This MUST be defined in order to get the in-scope extensions variables
	 */
	function __construct($ro_pointer)
	{
		parent::__construct($ro_pointer);
	}
	
	
}