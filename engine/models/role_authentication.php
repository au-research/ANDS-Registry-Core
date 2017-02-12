<?php
/*
Copyright 2011 The Australian National University
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

********************************************************************************
$Date: 2009-08-11 12:57:09 +1000 (Tue, 11 Aug 2009) $
$Revision: 32 $
*******************************************************************************/

class Role_authentication extends CI_Model {

	private $cosi_db = null;
	
    function __construct()
    {
        parent::__construct();
		$this->cosi_db = $this->load->database('roles', TRUE);
    }

    /**
     * Return an array containing the success/failure of authentication
     * using the parameters below. If a valid combination is supplied, also 
     * supplied a list of activities, functional and organisational roles
     * which are associated with that user as well as other details specific to the
     * authentication method (such as LDAP information, name, token, etc).  
     * 
     * @param $username Username to authenticate
     * @param $password Plaintext password to use to authenticate
     * @param $method Authentication method to use (built-in/ldap/shib...etc)
     */
    function authenticate($username, $password, $method=gCOSI_AUTH_METHOD_SHIBBOLETH)
    {

    	$result = $this->cosi_db->get_where("roles", array("role_id"=>$username, "role_type_id"=>"ROLE_USER", "enabled"=>DB_TRUE ));

        if($result->num_rows() > 0){
			$method = trim($result->row(1)->authentication_service_id);

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

		} else {
            if($method==gCOSI_AUTH_METHOD_SHIBBOLETH){

                //if first shib login
                //check if there's an existing one
                $name = isset($_SERVER['displayName']) ? $_SERVER['displayName'] : 'No Name Given';
                if($name!='No Name Given') {
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
                } else {
                    //no name given
                    throw new Exception('Bad Credentials. No name given');
                }
            }
        }											
    	//return array('result'=>0,'message'=>json_encode($result));												
    	if ($method === gCOSI_AUTH_METHOD_BUILT_IN)
		{
			if ($username == '') {
				throw new Exception('Authentication Failed (0)');
			}
				
			if ($password == '') {
				throw new Exception('Authentication Failed (1)');
			}
			
    		$result = $this->cosi_db->get_where("roles", array("role_id"=>$username, "role_type_id"=>"ROLE_USER", "authentication_service_id"=>gCOSI_AUTH_METHOD_BUILT_IN, "enabled"=>DB_TRUE ));
    												
    		if ($result->num_rows() > 0) {
    			$valid_users = $this->cosi_db->get_where("authentication_built_in", array("role_id"=>$username, "passphrase_sha1"=>sha1($password) ));
    			if ($valid_users->num_rows() > 0) {
    				$user_results = $this->getRolesAndActivitiesByRoleID ($valid_users->row(1)->role_id);
    				
					return array(	
						'result'=>1,
                        'authentication_service_id'=>$method,
						'message'=>'Success',
						'user_identifier'=>$result->row(1)->role_id,
		    			'name'=>$result->row(1)->name,
                        'auth_domain' => gPIDS_IDENTIFIER_SUFFIX,
						'last_login'=>$result->row(1)->last_login,
						'activities'=>$user_results['activities'],
						'organisational_roles'=>$user_results['organisational_roles'],
						'functional_roles'=>$user_results['functional_roles']
					);
    			}
	    		else
	    		{
	    			// Invalid password
					throw new Exception('Authentication Failed (2)');
	    		}
    		}
    		
		}
		else if ($method === gCOSI_AUTH_METHOD_SHIBBOLETH) {
			if ($username == '') {
				throw new Exception('Authentication Failed (0)');
			}
			$user_results = $this->getRolesAndActivitiesByRoleID ($username);   				
			return array(	
							'result'=>1,
                            'authentication_service_id'=>$method,
    						'message'=>'Success',
                            'auth_method' => $method,
							'user_identifier'=>$username,
					    	'name'=>$result->row(1)->name,
                            'auth_domain' => 'aaf.edu.au',
    						'last_login'=>$result->row(1)->last_login,
    						'activities'=>$user_results['activities'],
    						'organisational_roles'=>$user_results['organisational_roles'],
    						'functional_roles'=>$user_results['functional_roles']
    					);			    		
		}
		else if($method === gCOSI_AUTH_METHOD_LDAP)
		{
			/*
			 * Try using the LDAP Authentication Methods
			 */
			$this->load->helper('ldap');
			if ($username == '')
			{
				throw new Exception('Authentication Failed (00)');
			}
				
			if ($password == '')
			{
				throw new Exception('Authentication Failed (01)');
			}

			
			$result = $this->cosi_db->get_where("roles",	
													array(
														"role_id"=>$username,
														"role_type_id"=>"ROLE_USER",
	  													"authentication_service_id"=>gCOSI_AUTH_METHOD_LDAP,	
														"enabled"=>DB_TRUE
													));
											
			if ($result->num_rows() > 0)
			{
				$LDAPAttributes = array();
				$LDAPMessage = "";
				$successful = authenticateWithLDAP($username, $password, $LDAPAttributes, $LDAPMessage);

				// if (count($LDAPAttributes) > 0)
                if($successful)
				{
					$user_results = $this->getRolesAndActivitiesByRoleID ($username);
					
					return array(	
									'result'=>1,
                                    'authentication_service_id'=>$method,
									'message'=>'Success',
									'user_identifier'=>$username,
					    			'name'=>(isset($LDAPAttributes['cn'][0]) ? $LDAPAttributes['cn'][0] : $result->row(1)->name), // implementation specific
									'auth_domain' => gCOSI_AUTH_LDAP_HOST,
                                    'last_login'=>$result->row(1)->last_login,
									'activities'=>$user_results['activities'],
									'organisational_roles'=>$user_results['organisational_roles'],
									'functional_roles'=>$user_results['functional_roles']
								);
				}
	    		else
	    		{
	    			// LDAP ERROR (Could not bind)
	    			// You may wish to debug by appending $LDAPMessage to this response
					throw new Exception('Authentication Failed (02)');
	    		}
			}
			else
			{
				// No such user/disabled
				throw new Exception('Authentication Failed (03)');
			}
		}
    	else
		{
		return array('result'=>0,'message'=>json_encode($result));	
		}
		
		
    }
    
    
    public function register_last_login($role_id){
        $this->cosi_db->where('role_id', $role_id)->update('roles', array('last_login'=>date('Y-m-d H:i:s',time())));
    }
    
    public function getRolesAndActivitiesByRoleID ($role_id, $recursive = true)
    {
    	$ret = array('organisational_roles'=>array(), 'functional_roles'=>array(), 'activities'=>array());

        $superadmin = false; // superadmins inherit all roles/functions

    	$roles = $this->getChildRoles($role_id, $recursive);

    	foreach ($roles AS $role)
   		{
    		if (trim($role['role_type_id']) == gCOSI_AUTH_ROLE_ORGANISATIONAL)
    		{
    			$ret['organisational_roles'][] = $role['role_id'];
    		}
    		else if (trim($role['role_type_id']) == gCOSI_AUTH_ROLE_FUNCTIONAL)
    		{
    			$ret['functional_roles'][] = $role['role_id'];
    			// $ret['activities'] = array_merge($ret['activities'], $this->getChildActivities($role['role_id']));

                // Check if we're a superuser
                if ($role['role_id'] == AUTH_FUNCTION_SUPERUSER)
                {
                    $superadmin = true;
                }
    		}
    					
    	}

        // Superadmins get all organisational roles
        if ($superadmin && $recursive)
        {
            function getOnlyRoleIds(&$item, $key) { $item = $item['role_id']; }
            $orgRoles = $this->getAllOrganisationalRoles();
            array_walk( $orgRoles, 'getOnlyRoleIds' );

            $ret['organisational_roles'] = array_merge($ret['organisational_roles'], $orgRoles);
        }
    	
    	return $ret;
    				
    }

    public function getRolesInAffiliate($affiliate){
        $roles = array();
        $user_role = $this->cosi_db->query("SELECT child_role_id FROM role_relations WHERE parent_role_id = '".$affiliate."'");
        foreach($user_role->result() as $r){
            $roles[] = $r->child_role_id;
        }
        return $roles;
    }

   public function getDOIAppIdsInAffiliate($affiliates){
        $doi_appids = array();
        $the_affilates_string = '';
        
        foreach($affiliates as $an_affiliate)
        {
            $the_affilates_string .= "'".$an_affiliate."', ";
        }
        $the_affilates_string = trim($the_affilates_string,", ");

        $user_appids = $this->cosi_db->query("SELECT role_relations.parent_role_id 
                                            FROM role_relations, roles
                                            WHERE role_relations.child_role_id IN (".$the_affilates_string.")
                                            AND role_relations.parent_role_id = roles.role_id 
                                            AND roles.role_type_id = 'ROLE_DOI_APPID'");
        foreach($user_appids->result() as $r){
            $doi_appids[] = $r->parent_role_id;
        }
        return $doi_appids;
    }

    public function getAllOrganisationalRoles(){
        $roles = array();
        $org_roles = $this->cosi_db->query("SELECT * FROM roles WHERE role_type_id='ROLE_ORGANISATIONAL' AND enabled= ".DB_TRUE." ORDER BY name ASC");
        foreach($org_roles->result() as $r){
            $roles[] = array("role_id"=>$r->role_id, "name"=>$r->name);
        }
        return $roles;
    }
    
    public function registerAffiliation($thisRole, $orgRole){
        $insertQry = 'INSERT INTO role_relations (parent_role_id,child_role_id,created_who) VALUES (\''.$orgRole.'\',\''.$thisRole.'\',\''.$thisRole.'\');';
        $query = $this->cosi_db->query($insertQry);
        if($query){
            return true;
        }else{
            return false;
        }
    }

    public function createOrganisationalRole($orgRole, $thisRole, $name=false){
        $insertQry = 'INSERT INTO roles (role_id,role_type_id,name,enabled,created_who) VALUES (\''.$orgRole.'\',\'ROLE_ORGANISATIONAL\',\''.($name ? $name : $orgRole).'\',\'TRUE\',\''.$thisRole.'\');';
        $query = $this->cosi_db->query($insertQry);
        if($query){
            return true;
        }else return false;
    }

    public function updatePassword($username, $password)
    {
        $this->cosi_db->where("role_id", $username)->update("authentication_built_in", array('passphrase_sha1' => sha1($password)));
        return TRUE;
    }
    
    
    private function getChildRoles($role_id, $recursive = true, $prev = array())
    {
    	$roles = array();
    	
    	$related_roles = $this->cosi_db
                ->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
                ->from('role_relations')
                ->join('roles', 'roles.role_id = role_relations.parent_role_id')
                ->where('role_relations.child_role_id', $role_id)
                ->where('enabled', DB_TRUE)
                ->where('role_relations.parent_role_id !=', $role_id)
                ->get();
    	
    	foreach($related_roles->result() AS $row)
    	{
    		$roles[] = array("role_id" => $row->parent_role_id, "role_type_id" => $row->role_type_id);
    		if($recursive && !in_array($row->parent_role_id, $prev)) {
                array_push($prev, $row->parent_role_id);
                $child = $this->getChildRoles($row->parent_role_id, $recursive, $prev);
                if(sizeof($child) > 0) {
                    $roles = array_merge($roles, $this->getChildRoles($row->parent_role_id, $recursive, $prev));
                }
            }
    	}
    	
    	return $roles;
    }
    
	    
    private function getChildActivities($role_id)
    {
    	$activities = array();
    	
    	$results = $this->cosi_db->get_where("role_activities", array("role_id"=>$role_id));
    	foreach($results->result() AS $row)
    	{
    		$activities[] = $row->activity_id;
    	}
    	
    	return $activities;
    }

   

}