<?php
/**
 * Profile Controller
 * Used for MyRDA functionality
 * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class Profile extends MX_Controller
{

    /**
     * Index page for the profile
     * Same for the dashboard
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return view
     */
    public function index()
    {
        if ($this->user->isLoggedIn()) {
            $this->load->model('portal_user');
            // $user = $this->portal_user->getCurrentUser();

            monolog(
                array(
                    'event' => 'portal_dashboard'
                ),'portal', 'info'
            );
            $this->blade
                 // ->set('user', $user)
                 ->set('title', 'MyRDA - Research Data Australia')
                 ->set('scripts', array('profile_app'))
                 ->render('profile/dashboard2');
        } else {
            redirect('profile/login');
        }
    }

    /**
     * Return user data
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return json
     */
    public function get_user_data()
    {
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
                'message' => 'User is not logged in',
            ));
        }
    }

    /**
     * Sample test function
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return json
     */
    public function test()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $this->load->model('portal_user');

        $user = $this->portal_user->getCurrentUser();

        $saved_record = $user->user_data['saved_record'];
        $saved_record['list'] = array('1', '2', '3');

        echo json_encode($saved_record);

        $list = array(
            'name' => 'A Single List',
            'created' => date("Y-m-d H:i:s"),
            'records' => array(),
        );

    }

    /**
     * Check if a particular record has been bookmarked by the current user
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param  int  $id registry object ID
     * @return boolean
     */
    public function is_bookmarked($id)
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $this->load->model('portal_user');
//        dd($this->portal_user->has_saved_record($id));
        $saved_data = [];
        if ($this->portal_user->has_saved_record($id)) {
            $saved_data[] = array(
                'id' => $id,
                'last_viewed' => time());
            $msg = $this->portal_user->modify_user_data('saved_record', 'modify', $saved_data);
            echo json_encode(array('status' => 'OK', 'message' => 'IS BOOKMARKED'));

        } else {
            echo json_encode(array('status' => 'ERROR', 'message' => 'IS NOT BOOKMARKED'));
        }

    }

    /**
     * Adding user data
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param string $type [saved_search|saved_record]
     */
    public function add_user_data($type)
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $data = json_decode(file_get_contents("php://input"), true);
        $data = $data['data'];

        $this->load->model('portal_user');
        $message = array('status' => 'OK');

        //prepare the data to be saved
        if ($type == 'saved_search') {
            $data = array(
                'query_string' => $data,
            );
            $saved_data = array(
                'type' => $type,
                'value' => $data,
            );
            $message['saved_data'] = $saved_data;

            $event = array(
                'event' => 'portal_save_search',
            );
            $event = array_merge($event, $saved_data);
            monolog($event, 'portal', 'info');

            $this->portal_user->add_user_data($saved_data);
        }if ($type == 'saved_record') {
            foreach ($data as $d) {
                if (!$this->portal_user->has_saved_record($d['id'], $d['folder'])) {
                    $saved_data = array(
                        'type' => $type,
                        'value' => array('folder' => $d['folder'], 'id' => $d['id'], 'slug' => $d['slug'], 'class' => $d['class'], 'type' => $d['type'], 'url' => portal_url($d['slug'] . '/' . $d['id']), 'title' => $d['title'], 'saved_time' => $d['saved_time']),
                    );

                    $event = array(
                        'event' => 'portal_save_record',
                        'roid' => $d['id']
                    );
                    monolog($event,'portal', 'info');

                    $this->portal_user->add_user_data($saved_data);
                } else {
                    $message['status'] = 'error';
                    $message['info'] = 'record with ' . $d['title'] . ' already bookmarked';
                }
            }
        }
        echo json_encode($message);
    }

    /**
     * Modifying existing user data
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @param  string $type   [saved_search|saved_record]
     * @param  string $action
     * @return json
     */
    public function modify_user_data($type, $action)
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');
        $data = json_decode(file_get_contents("php://input"), true);
        $data = $data['data'];
        $this->load->model('portal_user');
        $message = $this->portal_user->modify_user_data($type, $action, $data);

        monolog([
            'event' => 'portal_modify_user_data',
            'profile_action' => [
                'type' => $type,
                'action' => $action,
                'data' => $data
            ]
        ],'portal', 'info');

        echo json_encode($message);
    }

    /**
     * Return the current user and their current data
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return json
     */
    public function current_user()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        $this->load->model('portal_user');
        $user = $this->portal_user->getCurrentUser();

        //fix silly functions encoding
        $functions = array();
        foreach ($user->function as $f) {
            array_push($functions, $f);
        }

        $user->function = $functions;

        echo json_encode($user);
    }

    public function get_specific_user()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
        set_exception_handler('json_exception_handler');

        $identifier = $this->input->get('identifier');

        if (!$identifier) {
            throw new Exception('identifier required');
        }

        $this->load->model('portal_user');
        $user = $this->portal_user->getSpecificUser($identifier);
        echo json_encode($user);
    }

    public function dashboard()
    {
        $this->index();
    }

    /**
     * Save the cookie for redirect authentication
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return void
     */
    private function save_auth_cookie()
    {
        $this->load->helper('cookie');
        if (isset($_SERVER['HTTP_REFERER'])) {
            // CC-1294 Use "set_cookie", not "setcookie".
            set_cookie("auth_redirect", $_SERVER['HTTP_REFERER'], time() + 3600);
        }

        if ($this->input->get('redirect')) {
            delete_cookie('auth_redirect');
            // CC-1294 Use "set_cookie", not "setcookie".
            set_cookie('auth_redirect', $this->input->get('redirect'), time() + 3600);
        }
    }

    /**
     * Return the login view for user
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return view
     */
    public function login()
    {

        if ($this->user->isLoggedIn()) {
            redirect('profile/#!/dashboard');
        }

        $this->save_auth_cookie();
        $authenticators = array(
            'built-in' => array(
                'slug' => 'built_in',
                'display' => 'Built In',
            ),
            'ldap' => array(
                'slug' => 'ldap',
                'display' => 'LDAP',
            ),
            'social' => array(
                'slug' => 'social',
                'display' => 'Social',
            ),
            'aaf' => array(
                'slug' => 'aaf',
                'display' => 'Australian Access Federation Login',
            ),
        );

        $default_authenticator = false;
        foreach ($authenticators as $auth) {
            if (isset($auth['default']) && $auth['default'] === true) {
                $default_authenticator = $auth['slug'];
                break;
            }
        }
        if (!$default_authenticator) {
            $default_authenticator = 'social';
        }

        $this->blade
             ->set('authenticators', $authenticators)
             ->set('default_authenticator', $default_authenticator)
             ->set('scripts', array('login'))
             ->render('profile/login');
    }

    /**
     * Logs the user out of the system
     * @author Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
     * @return redirect
     */
    public function logout()
    {
        if ($this->user->isLoggedIn()) {
            $this->load->helper('cookie');
            delete_cookie("auth_redirect");
            if (!session_id()) {
                session_start();
            }

            $this->session->sess_destroy();
            redirect('profile/login');
        } else {
            redirect('profile/login');
        }
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->library('blade');
    }
}
