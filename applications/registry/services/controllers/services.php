<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

define('SERVICES_MODULE_PATH', REGISTRY_APP_PATH.'services/');
/**
 * Services controller
 * 
 * Abstract services controller allows for easy extension of the
 * services module and logging and access management of requests
 * via the API key system. 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @package ands/services
 * 
 */
class Services extends MX_Controller {
	
	var $reserved_pages = array('register','query_schema');
	
	public function _remap($api_key, $params = array())
	{
		$this->config->load('services');
		$service_mapping = parse_ini_file(SERVICES_MODULE_PATH . "config.ini", true);
		// log_message('debug', 'Services request received from ' . $_SERVER["REMOTE_ADDR"]);
		// log_message('debug', 'Request URI: ' . $_SERVER["REQUEST_URI"]);
		
		// If no parameters supplied, display the services landing page!
		if ($api_key == "index"){
			$this->service_list();
			return;
		}else if($api_key == "documentation"){
			$this->documentation($params);
			return;
		}
		else if (in_array($api_key, $this->reserved_pages) && method_exists($this, $api_key))
		{
			// Some pre-canned pages (such as the registration module will have methods defined in this class)
			$this->{$api_key}();
			return;
		}
		
		// Method i.e. "getRIFCS", Format i.e. "xml"
		list($method, $format, $options) = $this->parse_request_params($params);
		
		if (is_null($format) && isset($service_mapping[$method]['default_format']))
		{
			$format = $service_mapping[$method]['default_format'];
		}

		// Setup our formatter
		global $formatter;
		$formatter = $this->getFormatter($format);

		// Allow it to grab exceptions and serve them appropriately!
		set_exception_handler(function($exception) {
			global $formatter;
		 	$formatter->error($exception->getMessage());
		});

		if (!$this->check_compatibility($method, $format, $service_mapping))
		{
			$formatter->error("Your requested method does not support this format: " . $format);
			$this->registerServiceRequest($api_key, $params, FAILURE, "Unsupported Format");
			return;
		}
		
		// Check that the API key is valid
		if (!$this->authenticate_api_key($api_key))
		{
			$formatter->error("Your API key does not exist or is invalid: " . $api_key);
			$this->registerServiceRequest($api_key, $params, FAILURE, "Invalid API Key");
			return;
		}
		
		$options = $service_mapping[$method];
		$handler = $this->getMethodHandler($service_mapping[$method]['method_handler']);
		$handler->initialise($options, $this->input->get(), $formatter);
		$this->output->set_content_type($formatter->output_mimetype());

		// All the setup is finished! Palm off the handling of the request...
		ob_start();
		$status = ($handler->handle($params) ? SUCCESS : FAILURE);
		$this->output->set_output(ob_get_clean());

		// Log this request
		$this->registerServiceRequest($api_key, $params, $status);

		unset($formatter);
		restore_error_handler();
	}

	private function service_list(){
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Web Services';
		$this->load->view('service_list', $data);
	}

	private function documentation($doc=''){
		$this->load->view('documentations/'.$doc[0]);
	}
	
	private function register()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Web Services';
		if (!$this->input->post('submit'))
		{
			$this->load->view('register_api_key', $data);
		}
		else
		{
			if (!$this->input->post('organisation') || !$this->input->post('contact_email'))
			{
				throw new Exception("One of the mandatory fields (Organisation name or Contact Email) were not entered. Please try again.");
			}
			else
			{
				// Generate a random API key hash
				$api_key = substr(md5(mt_rand()), 0, 12);

				$query = $this->db->get_where('api_keys', array('api_key'=>$api_key));
				if ($query->num_rows == 0)
				{
					$this->db->insert('api_keys',
						array(	'api_key' => $api_key, 
								'owner_email'=>$this->input->post('contact_email'),
								'owner_organisation'=>$this->input->post('organisation'),
								'owner_purpose'=>$this->input->post('purpose'),
								'created'=>time()
					));
				}
				else
				{
					throw new Exception("API Key could not be generated (numeric error = ".$api_key."). Please try again.");
				}

				$data["api_key"] = $api_key;
				$data["organisation"] = $this->input->post('organisation');

				$this->load->view('show_api_key', $data);

			}
		}
	}

	public function query_schema()
	{
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['title'] = 'Query Schema Fields (SOLR)';
		$this->load->view('solr_schema_fields', $data);
	}
	
	private function check_compatibility($method, $format, array $service_mapping)
	{
		if (array_key_exists($method, $service_mapping)
			&& is_array($service_mapping[$method]['supports']) 
			&& in_array($format, $service_mapping[$method]['supports']))
		{
			return true;
		}
		else
		{
			return false;
		}	
	}
	
		
		
	private function authenticate_api_key($api_key)
	{
		// Do the API key checking here!
		if (strlen($api_key) <= 15 & ctype_alnum($api_key))
		{
			// Pretty straightforward check of api_keys table for match
			$query = $this->db->get_where('api_keys',array('api_key'=>$api_key));
			if ($query->num_rows() > 0)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return false;
		}
	}
	
	private function parse_request_params(array $params)
	{

		// Get the default values (partially malformed requests)
		$method = $this->config->item('services_default_method');
		$format = null;

		// Grab the values from the parameter array
		// The syntax should be: <method>.<format>/?<query params>
		if (($called_method = array_shift($params)) != NULL)
		{
			$called_method = explode(".",$called_method);
			
			if ($called_method[0])
			{
				$method = $called_method[0];
			}
			if (isset($called_method[1]) && $called_method[1])
			{
				$format = $called_method[1];
			}
		}
		// The remaining params get passed along to the query
		$query_params = array_shift($params);
		
		return array($method, $format, $query_params);
	}
	
	
	private function getFormatter($format)
	{
		$formatter = null;
		
		if ($format && ctype_alnum($format))
		{
			
			$path = SERVICES_MODULE_PATH . '/interfaces/' . strtolower($format) . '.php';
			if (file_exists($path))
			{
				require_once($path);
				$classname = $format . "interface";
				$formatter = new $classname;
			}
			else
			{
				throw new Exception("Invalid format. Could not load the formatting parser for: '" . $format . "'");
			}
		}
		else
		{
			throw new Exception("Invalid Formatter -- cannot continue");
		}
		
		return $formatter;
	}

	
	private function getMethodHandler($method)
	{
		$handler = null;
		if ($method)
		{
			
			$path = SERVICES_MODULE_PATH . '/method_handlers/' . strtolower($method) . '.php';
			if (file_exists($path))
			{
				require_once($path);
				$classname = $method . "method";
				$handler = new $classname;
			}
			else
			{
				throw new Exception("Invalid handler. Could not load the method handler for: '" . $method . "'");
			}
		}
		else
		{
			throw new Exception("Invalid Method handler -- cannot continue");
		}
		
		return $handler;
	}
	
	/**
	 * Register a service request and essential information
	 * about the request for statistical purposes
	 *
	 * @param note string 	optionally specifies a note to 
	 *						be appended to the entry
	 */
	private function registerServiceRequest($api_key, $params, $status, $note=null)
	{
		$values = array();

		// The server time of the request
		$values['timestamp'] = time();

		$values['status'] = $status;

		// Details about the user that browsed here
		$values['ip_address'] = $this->input->ip_address();

		$values['api_key'] = $api_key;
		$values['service'] = implode($params,"&");
		$values['params'] = http_build_query(is_array($this->input->get()) ? $this->input->get() : array());

		// Optionally, a note for whatever use...
		if ($note) { $values['note'] = $note; }

		$this->db->insert('api_requests', $values);
	}
	
}	