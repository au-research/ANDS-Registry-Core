<?php
class Auth extends CI_Controller {

	public function login(){
		$data['title'] = 'Login';
		$data['js_lib'] = array('core', 'angular129');
		$data['scripts'] = array('login');

		$data['authenticators'] = array(
			'built-in' => array(
				'slug'		=> 'built_in',
				'display' 	=> 'Built In',
				'view' 		=>  $this->load->view('authenticators/built_in', false, true)
			),
			'ldap' => array(
				'slug'		=> 'ldap',
				'display' 	=> 'LDAP',
				'view' 		=>  $this->load->view('authenticators/ldap', false, true)
			)
		);

		if(get_config_item('shibboleth_sp')) {
			$shibboleth_sp =  array(
				'slug'		=>'shibboleth_sp',
				'display'	=> 'Shibboleth SP',
				'view'		=> $this->load->view('authenticators/shibboleth_sp', false, true)
			);
			array_push($data['authenticators'], $shibboleth_sp);
		}

		// var_dump(get_config_item('aaf_rapidconnect_url'));
		// var_dump(get_config_item('aaf_rapidconnect_secret'));

		if(get_config_item('aaf_rapidconnect_url') && get_config_item('aaf_rapidconnect_secret')) {
			$rapid_connect = array(
				'slug'		=> 'aaf_rapid',
				'default'	=> true,
				'display' 	=> 'AAF Rapid Connect',
				'view' 		=>  $this->load->view('authenticators/aaf_rapid', false, true)
			);
			array_push($data['authenticators'], $rapid_connect);
		}

		$data['default_authenticator'] = false;
		foreach($data['authenticators'] as $auth) {
			if(isset($auth['default']) && $auth['default']===true) {
				$data['default_authenticator'] = $auth['slug'];
				break;
			}
		}
		if(!$data['default_authenticator']) $data['default_authenticator'] = 'built_in';

		$this->load->view('login', $data);
	}

	public function authenticate($method = 'built_in') {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$authenticator_class = $method.'_authenticator';
		
		if (!file_exists('engine/models/authenticators/'.$authenticator_class.'.php')) {
			throw new Exception('Authenticator '.$authenticator_class.' not found!');
		}

		//get parameters from angularjs POST
		$params = json_decode(file_get_contents('php://input'), true);

		if(!$params) $params = array();
		$post = ($this->input->post() ? $this->input->post() : array());

		//get parameters from POST
		$params = array_merge($params, $post);

		try {
			$this->load->model('authenticators/'.$authenticator_class, 'auth');
			$this->auth->load_params($params);
			$response = $this->auth->authenticate();
			$this->user->refreshAffiliations($this->user->localIdentifier());
		} catch (Exception $e) {
			$this->auth->post_authentication_hook();
			// throw new Exception($e->getMessage());
		}
		
	}

	public function oauth(){
		if ($_SERVER['REQUEST_METHOD'] === 'GET'){
			$_GET = $_REQUEST;
		}
		require_once FCPATH.'/assets/lib/hybridauth/index.php';
	}
	
	public function logout(){
		// Logs the user out and redirects them to the homepage/logout confirmation screen
		$this->user->logout(); 		
	}
	
	//MAYBE DEPRECATED as of R14
	public function setUser(){
		$sharedToken = '';
		$data['title'] = 'Login';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$this->CI =& get_instance();
		$data['redirect'] = '';
		$data['authenticators'] = array(gCOSI_AUTH_METHOD_BUILT_IN => 'Built-in Authentication', gCOSI_AUTH_METHOD_LDAP=>'LDAP');
		if (get_config_item('shibboleth_sp')=='true') {
			$data['authenticators'][gCOSI_AUTH_METHOD_SHIBBOLETH] = 'Australian Access Federation (AAF) credentials';
			$data['default_authenticator'] = gCOSI_AUTH_METHOD_SHIBBOLETH;
		} else {
			$data['default_authenticator'] = gCOSI_AUTH_METHOD_BUILT_IN;
		}

		if(isset($_SERVER['shib-shared-token'])){
			$sharedToken = $_SERVER['shib-shared-token'];//authenticate using shared token
		} elseif (isset($_SERVER['persistent-id'])) {
			$sharedToken = sha1($_SERVER['persistent-id']);
			echo $sharedToken;
		} else {
			$data['error_message'] = "Unable to login. Shibboleth IDP was not able to authenticate the given credentials. Missing shared token or persistent id";
			$this->load->view('login', $data);
		}

		if($sharedToken) {
			try {
				if($this->user->authChallenge($sharedToken, '')) {
					if($this->input->get('redirect')!='auth/dashboard/'){
						redirect($this->input->get('redirect'));
					} else {
						redirect(registry_url().'auth/dashboard');
					}
				} else {
					$data['error_message'] = "Unable to login. Please check your credentials are accurate.";
					$this->load->view('login', $data);
				}
			}
			catch (Exception $e) {
				$data['error_message'] = "Unable to login. Please check your credentials are accurate.";
				$data['exception'] = $e;
				$this->load->view('login', $data);
			}
		}
	}

	public function registerAffiliation($new = false){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$orgRole = $this->input->post('orgRole');
		$thisRole = $this->input->post('thisRole');
		$jsonData = array();
		$this->load->model($this->config->item('authentication_class'), 'auth');

		if($new){
			$this->auth->createOrganisationalRole($orgRole, $thisRole);
		}

		if(in_array($orgRole, $this->user->affiliations())){
			$jsonData['status']='WARNING';
			$jsonData['message']='You are already affiliate with this organisation: '.$orgRole;
		}else{
			if($this->auth->registerAffiliation($thisRole, $orgRole)){
				$this->user->refreshAffiliations($thisRole);
				$jsonData['status']='OK';
				$jsonData['message']='registering success';
			}else{
				$jsonData['status']='ERROR';
				$jsonData['message']='problem encountered while registering affiliation';
			}
		}
		
		//$jsonData['message'].=$thisRole. ' affiliates with '.$orgRole;
		echo json_encode($jsonData);

		//sending email
		$this->load->library('email');
		$this->email->from($this->config->item('vocab_admin_email'), 'ANDS Vocabulary Notification');
		$this->email->to($this->config->item('vocab_admin_email')); 
		$this->email->subject('New user affiliation registered');
		$message = 'Registering user '.$thisRole. ' to affiliate with '.$orgRole;
		if($new) $message.='. User created '.$orgRole;
		$this->email->message($message);	
		$this->email->send();
	}
	
	public function dashboard()
	{
		$data['title'] = 'ANDS Online Services Home';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();
		$data['available_organisations'] = array();
		$data['group_vocabs'] = array();
		if($this->user->loggedIn()) 
		{
			if(sizeof($this->user->affiliations())>0){
				$data['hasAffiliation']=true;
			}else $data['hasAffiliation']=false;
			
			if (mod_enabled('vocab_service'))
			{
				$this->load->model('apps/vocab_service/vocab_services','vocab');
				$data['group_vocabs']=$this->vocab->getGroupVocabs();
				//$data['owned_vocabs']=$this->vocab->getOwnedVocabs(false);
				$this->load->model($this->config->item('authentication_class'), 'auth');
				$data['available_organisations'] = $this->auth->getAllOrganisationalRoles();
				asort($data['available_organisations']);
			}

			if (mod_enabled('registry'))
			{
				$db = $this->load->database( 'registry', TRUE );
				$this->db = $db;

				$this->load->model('data_source/data_sources','ds');
				$data['data_sources']=$this->ds->getOwnedDataSources(false, true);
			}

			$this->load->view('dashboard', $data);
		}
		else 
		{
			redirect('auth/login');
		}
	}

	public function getRecentlyUpdatedRecords()
	{
		$db = $this->load->database( 'registry', TRUE );
		$this->db = $db;

		$this->load->model('data_source/data_sources','ds');
		$data['data_sources']=$this->ds->getOwnedDataSources();

		$ds_ids = array(); foreach($data['data_sources'] AS $ds) { $ds_ids[] = $ds->id; }

		$data['recent_records'] = array();
		if ($ds_ids)
			{
			// Get recently updated records
			$query = $db->select('ro.registry_object_id, ro.status, ro.title, ra.value AS updated')
				->from('registry_object_attributes ra')
				->join('registry_objects ro',
					'ro.registry_object_id = ra.registry_object_id')
				->where('ra.attribute','updated')
				->where('ra.value >=', time() - (ONE_WEEK))
				->where_in('ro.data_source_id', $ds_ids)
				->limit(6)->order_by('value','desc');
			$query = $query->get();

			if($query->num_rows() > 0)
			{
				foreach($query->result() AS $row)
				{
					$data['recent_records'][] = $row;
				}
			}
		}
		$this->load->view('dashboard_records', $data);
	}
	
	public function printData($title, $internal_array)
	{
		if( $internal_array )
		{
			print '<b>'.$title."</b><br />\n";
			foreach($internal_array as $key => $value)
			{
				print("$key=");	
				if( is_array($value) )
				{
					foreach( $value as $subvalue )
					{
						print("$subvalue, ");
					}
				}
				else
				{
					print($value);
				}
				print "<br />\n";			
			}
		}
	}

	/* Interface for COSI built-in users to change their password from the default */
	public function change_password()
	{
		$data['title'] = 'Change Built-in Password';
		$data['js_lib'] = array('core');
		$data['scripts'] = array();

		if (!$this->user->loggedIn() || !$this->user->authMethod() == gCOSI_AUTH_METHOD_BUILT_IN)
		{
			throw new Exception("Unable to change password unless you are logged in as a built-in COSI user!");
		}

		// if ($this->config->item('authentication_class') != 'cosi_authentication')
		// {
		// 	throw new Exception("Unable to change password unless the authentication framework is COSI!");
		// }

		if ($this->input->post('password'))
		{
			if ($this->input->post('password') != $this->input->post('password_confirm'))
			{
				$data['error'] = "Both passwords must match! Please try again...";
			}
			elseif (strlen($this->input->post('password')) < 6)
			{
				$data['error'] = "Password must be 6 characters or longer! Please try again...";
			}
			else
			{
				$this->load->model($this->config->item('authentication_class'), 'role');
				$this->role->updatePassword($this->user->localIdentifier(), $this->input->post('password'));
				$this->session->set_flashdata('message', 'Your password has been updated. This will be effective from your next login.');
				redirect('/');
			}
		}

		$this->load->view('change_password_form', $data);
		
	}

}