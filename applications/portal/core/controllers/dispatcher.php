<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The dispatcher catches all requests that pass through the (:any) filter 
 * in the routes.php configuration for this application. 
 *
 * It's purpose is to map any requests that fail to match a specific module
 * and/or controller to the default view controller. This effectively means
 * that we can treat any "unknown" request as if it were a request to a SLUG
 * (so http://myapp/my_random_record_slug is treated as a view request).
 *
 * Note: Only a PUBLISHED record with a matching SLUG will be returned. 
 * 
 * @author Ben Greenwood <ben.greenwood@anu.edu.au>
 */
class Dispatcher extends MX_Controller {

	public function __construct()
    {
         parent::__construct();
    }

	public function _remap($method, $params = array())
	{
		// Put the method back together and try and locate a matching controller
		array_unshift($params, $method);
		$requested_controller = CI::$APP->router->locate($params);

		if(!is_null($requested_controller)) {
			echo Modules::run(implode("/",$params));
			return;
		} else if ($params[0] == "preview") {
			if(sizeof($params) > 2) {
				$_GET['slug'] = $params[1];
				$_GET['id'] = $params[2];
			} elseif(sizeof($params)==2) {
				$_GET['slug'] = array_pop($params);
			}

			if (!isset($_GET['slug'])) 
			{
				$_GET['slug'] = array_pop($params);
				if ($_GET['slug'] == "preview")
				{
					$_GET['slug'] = null;
				}
			}

			$params = array("view","preview");
			echo Modules::run(implode("/",$params));
		} else if($params[0]=='search') {
			$action_model = $this->config->item('default_model').'/search';
			$params = array($action_model);
			echo Modules::run(implode("/",$params));
			return;
		} else {

			// If no match, assume it is a SLUG view request
			if(sizeof($params) > 1) {
				$_GET['slug'] = $params[0];
				$_GET['id'] = $params[1];
			} elseif(sizeof($params)==1) {
				$_GET['any'] =$params[0];
			}

			// Quick fix for missing slash (might not work on "domain root" installations?)
			if(isset($_GET['slug'])) {
				if ($this->config->item('active_application') == $_GET['slug'] || !$_GET['slug']) {
					echo Modules::run("home");
					return;
				}
			}
			
			//view
			$action_model = $this->config->item('default_model').'/view';
			$params = array($action_model);
			echo Modules::run(implode("/",$params));
			return;
		}
	}

}