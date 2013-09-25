<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
spl_autoload_register(function($className) {
    if (substr($className, 0, 8) === 'DebugBar') {
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
        require_once 'assets/lib/'.$filename;
    }
    if (substr($className, 0, 3) === 'Psr') {
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
        require_once 'assets/lib/'.$filename;
    }
});
use DebugBar\StandardDebugBar;
use DebugBar\JavascriptRenderer;

class Debugbar {

	private $CI;
	private $config;
    private $debugbar;
    private $debugbarRenderer;

	function __construct(){
        $this->CI =& get_instance();
		$this->init();
    }

    function init(){
        $this->debugbar = new StandardDebugBar();
        $this->debugbarRenderer = $this->debugbar->getJavascriptRenderer();
    	return true;
    }

    function addMsg($msg){
        $this->debugbar["messages"]->addMessage($msg);
    }

    function debugbarRenderer(){
        return $this->debugbarRenderer;
    }
 
}