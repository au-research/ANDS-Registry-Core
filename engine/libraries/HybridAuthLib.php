<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require FCPATH.'/assets/lib/hybridauth/Hybrid/Auth.php';

class HybridAuthLib extends Hybrid_Auth
{
	function __construct($config = array())
	{
		$ci =& get_instance();
		$ci->load->helper('url_helper');
		$config = config_item('oauth_config');
		parent::__construct($config);
		// log_message('debug', 'HybridAuthLib Class Initalized');
	}

	/**
	 * @deprecated
	 */
	public static function serviceEnabled($service)
	{
		return self::providerEnabled($service);
	}

	public static function providerEnabled($provider)
	{
		return isset(parent::$config['providers'][$provider]) && parent::$config['providers'][$provider]['enabled'];
	}
}

/* End of file HybridAuthLib.php */
/* Location: ./application/libraries/HybridAuthLib.php */