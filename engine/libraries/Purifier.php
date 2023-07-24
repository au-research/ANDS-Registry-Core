<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
include_once('assets/lib/html_purifier/HTMLPurifier.auto.php');

class Purifier {

	private $CI;
	private $config;
    private $htmlPurifier;


	function __construct(){
        $this->CI =& get_instance();
		$this->init();
    }


    function init()
    {
        $this->config = HTMLPurifier_Config::createDefault();
        $this->config->set('Core.Encoding', (string) $this->CI->config->item('Core_Encoding')); // replace with your encoding
        $this->config->set('HTML.Doctype', $this->CI->config->item('HTML_Doctype')); // replace with your doctype
        $this->config->set('HTML.AllowedElements', $this->CI->config->item('HTML_AllowedElements')); // sets allowed html elements that can be used.
        $this->config->set('HTML.AllowedAttributes', $this->CI->config->item('HTML_AllowedAttributes')); // sets allowed html attributes that can be used.
        $this->config->set('Cache.DefinitionImpl', 'Serializer'); 
        $this->config->set('Cache.SerializerPath', '/tmp');
        $this->htmlPurifier = new HTMLPurifier($this->config);
    	return true;
    }


    function purify_html($dirty_html){
    	$clean_html = $this->htmlPurifier->purify($dirty_html);
        unset($this->htmlPurifier); $this->init(); // memory cleanup
        return $clean_html;
    }
 
}