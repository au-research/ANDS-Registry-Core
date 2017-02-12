<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class MethodHandler
{
	var $params, $options, $formatter; 
	
	function initialise($options, $params, $formatter)
	{
		$this->params = $params ?: array();
		$this->options = $options;
		$this->formatter = $formatter;
		
	}
    abstract protected function handle();
}
