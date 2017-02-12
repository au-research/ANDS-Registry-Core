<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/**
 * 
 */
class User {
	
	private $CI;
	
	// These can get accessed several times per page load, so store them 
	// here to avoid going back to the session every time
	private $functions;
	private $affiliations;

	/**
	 * 
	 */
    function authChallenge($username, $password) {
    	// Dynamically load the authentication_class (as defined in the config file)
    	$this->CI->load->model($this->CI->config->item('authentication_class'), 'auth');
		$login_response = $this->CI->auth->authenticate($username, $password);

		if ($login_response['result'] == 1) {
			// Set the user's identifier and friendly name to the session
			$this->CI->session->set_userdata(array(
				AUTH_USER_IDENTIFIER	 => $login_response['user_identifier'] . "::",
				AUTH_USER_FRIENDLY_NAME	 => $login_response['name'],
				AUTH_METHOD 			 =>	$login_response['authentication_service_id'],
				AUTH_DOMAIN 			 =>	$login_response['auth_domain']
			));
			$this->CI->auth->register_last_login($login_response['user_identifier']);
			// And extract the functions and affiliations							
			$this->appendFunction(array_merge(array(AUTH_FUNCTION_LOGGED_IN_ATTRIBUTE),$login_response['functional_roles']));
			$this->appendAffiliation($login_response['organisational_roles']);
			return true;
		} else {
			throw new Exception("Unable to authenticate user. Login object returned negative response.".$login_response['message']);
		}
		
		return false;
    }

    function authComplete($role){
    	$this->CI->session->set_userdata(array(
			AUTH_USER_IDENTIFIER	 => $role['user_identifier'] . "::",
			AUTH_USER_FRIENDLY_NAME	 => $role['name'],
			AUTH_METHOD 			 =>	$role['authentication_service_id'],
			AUTH_DOMAIN 			 =>	$role['auth_domain']
		));			
		
		//return all affiliations if you are super user
		$this->cosi_db = $this->CI->load->database('roles', true);
		$query = $this->cosi_db->get_where('roles', array('role_type_id'=>gCOSI_AUTH_ROLE_ORGANISATIONAL));
		if ($query->num_rows() > 0) {
			$aff = array();
			foreach ($query->result_array() as $r) {
				$aff[] = $r['role_id'];
			}
			set_cookie('affiliations', json_encode($aff, true));
			// $this->appendAffiliation($aff);
		}

		$this->appendFunction(array_merge(array(AUTH_FUNCTION_LOGGED_IN_ATTRIBUTE),$role['functional_roles']));
		$this->appendAffiliation($role['organisational_roles']);
    }
	
	/**
	 * Logout the current user, destroying their current session data
	 */
	function logout($redirect = false) {
		if(!session_id()) {
			session_start();
		}
		unset($this->session->userdata); 
		// clear local caches
		unset($this->functions);
		unset($this->affiliations);
		$this->CI->session->sess_destroy(); //???
		delete_cookie('redirect');
		delete_cookie('affiliations');
		delete_cookie('authentication');
		if ($redirect) {
			redirect($redirect);
		} else redirect('/auth/login/');
	}
	

	public function refreshAffiliations($role_id) {
		$this->CI->load->model($this->CI->config->item('authentication_class'), 'auth_class');
		$roles = $this->CI->auth_class->getRolesAndActivitiesByRoleID($role_id);
		if($roles){
			$this->appendFunction(array_merge(array(AUTH_FUNCTION_LOGGED_IN_ATTRIBUTE),$roles['functional_roles']));
			$this->appendAffiliation($roles['organisational_roles']);
		}
	}

	/**
	 * Return whether a user is authenticated or not
	 */
	function redirectLogin()
	{
		redirect('auth/login');
	}

	/**
	 * Return whether a user is authenticated or not
	 */
	function loggedIn()
	{
		return $this->hasFunction(AUTH_FUNCTION_LOGGED_IN_ATTRIBUTE);
	}

	function isLoggedIn()
	{
		return $this->loggedIn();
	}

	function isSuperAdmin()
	{
		return $this->hasAffiliation(AUTH_FUNCTION_SUPERUSER);
	}
	
	/**
	 * Return a user-friendly representation of the logged in user
	 */
	function name()
	{
		$name = $this->CI->session->userdata(AUTH_USER_FRIENDLY_NAME);
		if ($name)
		{
			return $name;
		}
		else
		{
			return AUTH_DEFAULT_FRIENDLY_NAME;	
		}
	}

	function profileImage() {

		//@todo: need to fix this for servers without roles
		return false;

		if ( $this->isLoggedIn() ) {
			$role_db = $this->CI->load->database('roles', TRUE);
			$result = $role_db->get_where('roles', array('role_id'=>$this->localIdentifier()));
			if ($result->num_rows() > 0) {
				$r = $result->first_row();
				if ($r->oauth_data) {
					$data = json_decode($r->oauth_data, true);
					if ($data['photoURL']) return $data['photoURL'];
				} else {
					return asset_url('images/generic_user.png', 'core');
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Return a unique identifier representing the logged in user
	 */
	function identifier()
	{
		$identifier = $this->CI->session->userdata(AUTH_USER_IDENTIFIER);
		if ($identifier)
		{
			return $identifier;
		}
		else
		{
			throw new Exception ("User identifier referenced, but not initialised. Perhaps the user is not logged in?");
		}
	}
	
	function authMethod()
	{
		return $this->CI->session->userdata(AUTH_METHOD);
	}	

	function authDomain()
	{
		return $this->CI->session->userdata(AUTH_DOMAIN);
	}	

	/**
	 * Return the local portion of the user's identifier
	 */
	function localIdentifier()
	{
		$id = $this->identifier();
		return substr($id,0, strpos($id, '::'));
	}

	function ownedDataSourceIDs() {
		$data_sources = array();
		$affiliations = $this->affiliations();
		if ((is_array($affiliations) && count($affiliations) > 0) || $this->hasFunction(AUTH_FUNCTION_SUPERUSER)) {
			$this->db = $this->CI->load->database('registry', true);
			if ($this->hasFunction(AUTH_FUNCTION_SUPERUSER)) {
				$query = $this->db->query("SELECT * FROM data_sources");	
			} else {
				$query = $this->db->where_in('record_owner', $affiliations)->get('data_sources');
			}

			if ($query->num_rows() == 0) {
				return $data_sources;
			} else {
				foreach($query->result_array() AS $ds) {
					$data_sources[] = $ds['data_source_id'];
				}
			}
		}
		return $data_sources;
	}


	/**
	 * 
	 */
	function functions()
	{
		return $this->functions;
	}
	
	/**
	 * 
	 */
	function appendFunction(array $function_list)
	{
		if ($this->CI->session->userdata(AUTH_FUNCTION_ARRAY))
		{
			$this->CI->session->set_userdata(AUTH_FUNCTION_ARRAY, array_unique(array_merge($function_list,$this->CI->session->userdata(AUTH_FUNCTION_ARRAY))));
		}
		else
		{
			$this->CI->session->set_userdata(AUTH_FUNCTION_ARRAY, $function_list);
		}
		$this->functions = $this->CI->session->userdata(AUTH_FUNCTION_ARRAY);
	}
	
	/**
	 * 
	 */
	function hasFunction($name)
	{
		// Add superuser capabilities
		if (in_array(AUTH_FUNCTION_SUPERUSER, $this->functions)) {
			return TRUE;
		}
		return in_array($name, $this->functions);
	}
	
		
		
	/**
	 * 
	 */
	function affiliations()
	{
		//@todo: fix this
		// dd($this->CI->session->all_userdata());
		$this->affiliations = $this->CI->session->userdata(AUTH_AFFILIATION_ARRAY);

		if ($cookie = get_cookie('affiliations')) {
			// $this->affiliations = json_decode($cookie, true);
		}

		return $this->affiliations;

		// $this->refreshAffiliations($this->localIdentifier());
		if ($this->hasFunction(AUTH_FUNCTION_SUPERUSER)) {
			//return all affiliations if you are super user
			$this->cosi_db = $this->CI->load->database('roles', true);
			$query = $this->cosi_db->get_where('roles', array('role_type_id'=>gCOSI_AUTH_ROLE_ORGANISATIONAL));
			if ($query->num_rows() > 0) {
				$aff = array();
				foreach ($query->result_array() as $r) {
					$aff[] = $r['role_id'];
				}
				return $aff;
			} else {
				return $this->affiliations;
			}
		} else {
			return $this->affiliations;
		}
	}

    /**
     * Construct an array that maps affiliations to
     * pretty affiliation names, each with both full name and abbreviation,
     * e.g., "ANDS" => "Australian National Data Service (ANDS)".
     */
    function affiliationsNames()
    {
        //@todo: fix this
        // dd($this->CI->session->all_userdata());
        $this->affiliationsName = $this->CI->session->userdata(AUTH_AFFILIATION_ARRAY);
        $this->cosi_db = $this->CI->load->database('roles', true);
        $affNames = array();
        foreach($this->affiliationsName as $affiliation){
            $query = $this->cosi_db->get_where('roles', array('role_id'=>$affiliation));
            if ($query->num_rows() > 0) {
                // There _can_ be more than one full name, so just pick the first for now.
                $affNames[$affiliation] = $query->result_array()[0]['name'] . ' (' . $affiliation . ')';
            } else {
                // No full name, so use the abbreviation only.
                $affNames[$affiliation] = $affiliation;
            }
        }
        return $affNames;
    }

	/**
	 * 
	 */
	function appendAffiliation(array $affiliation_list)
	{
		if ($this->CI->session->userdata(AUTH_AFFILIATION_ARRAY))
		{
			$this->CI->session->set_userdata(AUTH_AFFILIATION_ARRAY, array_unique(array_merge($affiliation_list,$this->CI->session->userdata(AUTH_AFFILIATION_ARRAY))));
		}
		else
		{
			$this->CI->session->set_userdata(AUTH_AFFILIATION_ARRAY, $affiliation_list);
		}

		$this->affiliations = $this->CI->session->userdata(AUTH_AFFILIATION_ARRAY);
	}
	
	/**
	 * 
	 */
	function hasAffiliation($name)
	{
		if ($this->functions && in_array(AUTH_FUNCTION_SUPERUSER, $this->functions))
		{
			return TRUE;
		}
		if ($this->affiliations)
		{
			if (in_array($name, $this->affiliations))
			{
				return true;
			}
		}
		return false;
	}

	function doiappids()
	{
 		$this->CI->load->model($this->CI->config->item('authentication_class'), 'auth');
		$doi_apps = $this->CI->auth->getDOIAppIdsInAffiliate($this->affiliations());
		if($doi_apps){	
   			
   			return $doi_apps;
   		}
	}

	function __construct()
    {
        $this->CI =& get_instance();
		$this->CI->load->library('session');
		$this->CI->load->helper('cookie');
		$this->init();
    }
	
	/**
	 * Initialise the user's functions and affiliations
	 */
	private function init()
	{
		if (!$this->CI->session->userdata(AUTH_AFFILIATION_ARRAY))
		{
			$this->CI->session->set_userdata(AUTH_AFFILIATION_ARRAY, array());
		}
		
		if (!$this->CI->session->userdata(AUTH_FUNCTION_ARRAY))
		{
			$this->CI->session->set_userdata(AUTH_FUNCTION_ARRAY, array(AUTH_FUNCTION_DEFAULT_ATTRIBUTE));
		}
		
		// Copy to the local variable to avoid repeat access!
		$this->functions = $this->CI->session->userdata(AUTH_FUNCTION_ARRAY);
		$this->affiliations = $this->CI->session->userdata(AUTH_AFFILIATION_ARRAY);
	}
}
/* End of file User.php */