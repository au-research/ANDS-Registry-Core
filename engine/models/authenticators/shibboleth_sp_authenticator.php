<?php

/**
 * Authenticator for Built in Accounts
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
require_once('engine/models/authenticator.php');
class Shibboleth_sp_authenticator extends Authenticator {

	public function authenticate() {
		$this->auth_domain = 'aaf.edu.au';

        if(isset($_SERVER['shib-shared-token'])) {
            $username = $_SERVER['shib-shared-token'];
        } elseif (isset($_SERVER['persistent-id'])) {
            $username = sha1($_SERVER['persistent-id']);
        }

		//update persistent-id
        if (isset($_SERVER['persistent-id'])){
            $this->cosi_db->where('role_id', $username);
            $this->cosi_db->update('roles', array('persistent_id'=>$_SERVER['persistent-id']));
        }

        //update email
        if (isset($_SERVER['mail'])) {
            $this->cosi_db->where('role_id', $username)->update('roles', array('email' =>$_SERVER['mail']));
        } elseif (isset($_SERVER['email'])) {
            $this->cosi_db->where('role_id', $username)->update('roles', array('email' =>$_SERVER['email']));
        }

        $name = isset($_SERVER['displayName']) ? $_SERVER['displayName'] : 'No Name Given';

        if ($name!='No Name Given') {
        	$result = $this->cosi_db->get_where('roles', array('name'=>$name, 'authentication_service_id'=>gCOSI_AUTH_METHOD_SHIBBOLETH));
            if($result->num_rows() > 0) {
                //there's an existing user, update the edupersontargetID
                $role_id = trim($result->row(1)->role_id);
                // log_message('info','role_id is '. $role_id);
                $username = $role_id;
                if(isset($_SERVER['persistent-id'])){
                    $this->cosi_db->where('role_id', $role_id);
                    $this->cosi_db->update('roles', array('persistent_id'=>$_SERVER['persistent-id']));
                }
                if (isset($_SERVER['mail'])) {
                   $this->cosi_db->where('role_id', $username)->update('roles', array('email' =>$_SERVER['mail']));
                } elseif (isset($_SERVER['email'])) {
                   $this->cosi_db->where('role_id', $username)->update('roles', array('email' =>$_SERVER['email']));
                }
            } else {
                //there's no user has the same name, create the user
                if (isset($_SERVER['mail'])) {
                   $email = $_SERVER['mail'];
                } elseif (isset($_SERVER['email'])) {
                   $email = $_SERVER['email'];
                } else $email = '';
                $data = array(
                    'role_id' => $username,
                    'role_type_id' => 'ROLE_USER',
                    'authentication_service_id'=>$method,
                    'enabled'=>DB_TRUE,
                    'name'=> $name,
                    'shared_token' => isset($_SERVER['shib-shared-token']) ? $_SERVER['shib-shared-token'] : '',
                    'persistent_id' => isset($_SERVER['persistent-id']) ? $_SERVER['persistent-id'] : '',
                    'email' => $email,
                );

                //send alert email to admin
                $subject = 'A new shibboleth user has been automatically registered';
                $message = 'A new shibboleth user with the name of '.$name. ' has been automatically registered.';
                if(isset($_SERVER['persistent-id'])) $message .= 'With the persistent ID of: '.$_SERVER['persistent-id'].'.';
                if(isset($_SERVER['shib-shared-token'])) $message .= 'With the shared token of: '.$_SERVER['shib-shared-token'].'.';
                if(isset($_SERVER['mail'])) $message .= 'With the email of: '.$email.'.';
                $to = get_config_item('site_admin_email');
                $headers  = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                mail($to, $subject, $message, $headers);

                $this->cosi_db->insert('roles', $data);
                $this->registerAffiliation($username, 'SHIB_AUTHENTICATED', 'SYSTEM');
                $result = $this->cosi_db->get_where("roles", array("role_id"=>$username, "role_type_id"=>"ROLE_USER", "enabled"=>DB_TRUE));
            }
        }

		$user = $result->row(1);
		$this->return_roles($user);
	}

    public function load_params($params) {
        $this->params = $params;
        $this->check_req();
    }

    private function check_req() {
        if(!isset($_SERVER['persistent-id'])) {
            throw new Exception('Persistent ID not found');
        }
        if(!isset($_SERVER['displayName'])) {
            throw new Exception('Display name not found');
        }
    }

    public function post_authentication_hook(){
        redirect('/auth/dashboard');
    }
}