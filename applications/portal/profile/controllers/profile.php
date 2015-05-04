<?php
class Profile extends MX_Controller {

	function index(){
		if($this->user->isLoggedIn()) {
			$this->load->model('portal_user');
			// $user = $this->portal_user->getCurrentUser();
			$this->blade
				// ->set('user', $user)
				->set('title', 'MyRDA - Research Data Australia')
				->set('scripts', array('profile_app'))
				->render('profile/dashboard2');
		} else {
			redirect('profile/login');
		}
	}

	

	public function get_user_data() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		if ($this->user->isLoggedIn()) {
			$this->load->model('portal_user');
			$user = $this->portal_user->getCurrentUser();
			$data = $user->portal_user->getUserData($user->identifier);
			echo json_encode($data);
		} else {
			echo json_encode(array(
				'status' => 'error',
				'message' => 'User is not logged in'
			));
		}
	}

	public function test(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
    	$this->load->model('portal_user');

    	$user = $this->portal_user->getCurrentUser();

    	$saved_record = $user->user_data['saved_record'];
    	$saved_record['list'] = array('1','2','3');

    	echo json_encode($saved_record);

    	$list = array(
    		'name' => 'A Single List',
    		'created' => date("Y-m-d H:i:s"),
    		'records' => array()
    	);

	}

    public function is_bookmarked($id) {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $this->load->model('portal_user');
//        dd($this->portal_user->has_saved_record($id));
        $saved_data = [];
        if($this->portal_user->has_saved_record($id)) {
            $saved_data[] = array(
                'id' => $id,
                'last_viewed' => time());
            $msg = $this->portal_user->modify_user_data('saved_record', 'modify', $saved_data);
            echo json_encode(array('status'=>'OK', 'message'=>'IS BOOKMARKED'));

        } else echo json_encode(array('status'=>'ERROR', 'message'=>'IS NOT BOOKMARKED'));
    }


	public function add_user_data($type) {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
		set_exception_handler('json_exception_handler');
		$data = json_decode(file_get_contents("php://input"), true);
		$data = $data['data'];

		$this->load->model('portal_user');
        $message = array('status' => 'OK');
		
		//prepare the data to be saved
		if ($type=='saved_search') {
			$data = array(
				'query_string' => $data
			);
			$saved_data = array(
				'type' => $type,
				'value' => $data
			);
            $message['saved_data'] = $saved_data;
			$this->portal_user->add_user_data($saved_data);
		} if ($type=='saved_record') {
			foreach($data as $d) {
				if (!$this->portal_user->has_saved_record($d['id'],$d['folder'])){
					$saved_data = array(
						'type' => $type,
						'value' => array('folder'=>$d['folder'], 'id'=>$d['id'], 'slug'=>$d['slug'], 'class'=>$d['class'], 'type'=>$d['type'], 'url' => portal_url($d['slug'].'/'.$d['id']), 'title'=>$d['title'], 'saved_time'=>$d['saved_time'])
					);
					$this->portal_user->add_user_data($saved_data);
				}
                else{
                    $message['status'] = 'error';
                    $message['info'] = 'record with '.$d['title'].' already bookmarked';
                }
			}
		}
        echo json_encode($message);
	}

    public function modify_user_data($type, $action) {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $data = json_decode(file_get_contents("php://input"), true);
        $data = $data['data'];
        $this->load->model('portal_user');
        $message = $this->portal_user->modify_user_data($type, $action, $data);
        echo json_encode($message);
    }



	public function current_user() {
		header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        
		$this->load->model('portal_user');
		$user = $this->portal_user->getCurrentUser();

		//fix silly functions encoding
		$functions = array();
		foreach($user->function as $f) array_push($functions, $f);
		$user->function = $functions;
		
		echo json_encode($user);
	}

	public function get_specific_user() {
		header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        $identifier = $this->input->get('identifier');

        if(!$identifier) throw new Exception('identifier required');

        $this->load->model('portal_user');
        $user = $this->portal_user->getSpecificUser($identifier);
        echo json_encode($user);
	}

	function dashboard() {
		$this->index();
	}

	private function save_auth_cookie() {
		$this->load->helper('cookie');
		if(isset($_SERVER['HTTP_REFERER'])) {
			setcookie("auth_redirect", $_SERVER['HTTP_REFERER'], time()+3600, '/');
		}

		if ($this->input->get('redirect')) {
			delete_cookie('auth_redirect');
			setcookie('auth_redirect', $this->input->get('redirect'), time()+3600, '/');
		}
	}

	function login() {

		if($this->user->isLoggedIn()) {
			redirect('profile/#!/dashboard');
		}

		$this->save_auth_cookie();
		$authenticators = array(
			'built-in' => array(
				'slug' => 'built_in',
				'display' => 'Built In'
			),
			'ldap' => array(
				'slug'		=> 'ldap',
				'display' 	=> 'LDAP',
			),
			'social' => array(
				'slug'		=> 'social',
				'display'	=> 'Social'
			),
			'aaf' => array(
				'slug' 		=> 'aaf',
				'display' 	=> 'Shibboleth AAF Rapid Connect'
			),
		);

		$default_authenticator = false;
		foreach($authenticators as $auth) {
			if(isset($auth['default']) && $auth['default']===true) {
				$default_authenticator = $auth['slug'];
				break;
			}
		}
		if(!$default_authenticator) $default_authenticator = 'social';

		$this->blade
			->set('authenticators', $authenticators)
			->set('default_authenticator', $default_authenticator)
			->set('scripts', array('login'))
			->render('profile/login');
	}

	function logout() {
		if($this->user->isLoggedIn()) {
			$this->load->helper('cookie');
			delete_cookie("auth_redirect");
			if(!session_id()) session_start();
			$this->session->sess_destroy();
			redirect('profile/login');
		} else {
			redirect('profile/login');
		}
	}

	public function __construct() {
		parent::__construct();
		$this->load->library('blade');
		$this->blade->set_template('omega');
	}
}