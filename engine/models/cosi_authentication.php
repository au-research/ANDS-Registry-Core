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

class Cosi_authentication extends CI_Model {

	private $cosi_db = null;
	
    function __construct()
    {
        parent::__construct();
		$this->cosi_db = $this->load->database('cosi', TRUE);
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
    	$result = $this->cosi_db->get_where("dba.tbl_roles",	
    												array(
    													"role_id"=>$username,
    													"role_type_id"=>"ROLE_USER",	
    													"enabled"=>'t'
    												));
		if($result->num_rows() > 0){
			$method = trim($result->row(1)->authentication_service_id);
		}
        else
        {
            if($method==gCOSI_AUTH_METHOD_SHIBBOLETH){
                //create user if this is the first shib login
                
                $data = array(
                    'role_id' => $username,
                    'role_type_id'=>'ROLE_USER',
                    'authentication_service_id'=>$method,
                    'enabled'=>DB_TRUE,
                    'name'=> $_SERVER['displayName']
                    //'email' => $_SERVER['mail']
                );

                if($username == $_SERVER['shib-shared-token']){
                    $this->cosi_db->insert('dba.tbl_roles',$data);
                }

                $result = $this->cosi_db->get_where("dba.tbl_roles",    
                                                    array(
                                                        "role_id"=>$username,
                                                        "role_type_id"=>"ROLE_USER",    
                                                        "enabled"=>'t'
                                                    ));
            }
        }
    												
    	//return array('result'=>0,'message'=>json_encode($result));												
    	if ($method === gCOSI_AUTH_METHOD_BUILT_IN)
		{
			if ($username == '')
			{
				throw new Exception('Authentication Failed (0)');
			}
				
			if ($password == '')
			{
				throw new Exception('Authentication Failed (1)');
			}
			
    		$result = $this->cosi_db->get_where("dba.tbl_roles",	
    												array(
    													"role_id"=>$username,
    													"role_type_id"=>"ROLE_USER",
      													"authentication_service_id"=>gCOSI_AUTH_METHOD_BUILT_IN,	
    													"enabled"=>'t'
    												));
    												
    		if ($result->num_rows() > 0)
    		{
    			$valid_users = $this->cosi_db->get_where("dba.tbl_authentication_built_in",
    													array(
    														"role_id"=>$username,
    														"passphrase_sha1"=>sha1($password)	
    													));	
    			if ($valid_users->num_rows() > 0)
    			{
    				$user_results = $this->getRolesAndActivitiesByRoleID ($valid_users->row(1)->role_id);
    				
					return array(	
									'result'=>1,
                                    'authentication_service_id'=>$method,
    								'message'=>'Success',
									'user_identifier'=>$result->row(1)->role_id,
					    			'name'=>$result->row(1)->name,
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
		else if ($method === gCOSI_AUTH_METHOD_SHIBBOLETH)
		{
            if(!isset($_SERVER['displayName'])){
                throw new Exception('Bad Credentials');
            }
			if ($username == '')
			{
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
			
			$result = $this->cosi_db->get_where("dba.tbl_roles",	
													array(
														"role_id"=>$username,
														"role_type_id"=>"ROLE_USER",
	  													"authentication_service_id"=>gCOSI_AUTH_METHOD_LDAP,	
														"enabled"=>'t'
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
    			$ret['activities'] = array_merge($ret['activities'], $this->getChildActivities($role['role_id']));

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
        $user_role = $this->cosi_db->query("SELECT child_role_id FROM dba.tbl_role_relations WHERE parent_role_id = '".$affiliate."'");
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

        $user_appids = $this->cosi_db->query("SELECT dba.tbl_role_relations.parent_role_id 
                                            FROM dba.tbl_role_relations, dba.tbl_roles
                                            WHERE dba.tbl_role_relations.child_role_id IN (".$the_affilates_string.")
                                            AND dba.tbl_role_relations.parent_role_id = dba.tbl_roles.role_id 
                                            AND dba.tbl_roles.role_type_id = 'ROLE_DOI_APPID'");
        foreach($user_appids->result() as $r){
            $doi_appids[] = $r->parent_role_id;
        }
        return $doi_appids;
    }

    public function getAllOrganisationalRoles(){
        $roles = array();
        $org_roles = $this->cosi_db->query("SELECT * FROM dba.tbl_roles WHERE role_type_id='ROLE_ORGANISATIONAL' AND enabled='t' ORDER BY name ASC");
        foreach($org_roles->result() as $r){
            $roles[] = array("role_id"=>$r->role_id, "name"=>$r->name);
        }
        return $roles;
    }
    
    public function registerAffiliation($thisRole, $orgRole){
        $insertQry = 'INSERT INTO dba.tbl_role_relations (parent_role_id,child_role_id,created_who) VALUES (\''.$orgRole.'\',\''.$thisRole.'\',\''.$thisRole.'\');';
        $query = $this->cosi_db->query($insertQry);
        if($query){
            return true;
        }else{
            return false;
        }
    }

    public function createOrganisationalRole($orgRole, $thisRole, $name=false){
        $insertQry = 'INSERT INTO dba.tbl_roles (role_id,role_type_id,name,enabled,created_who) VALUES (\''.$orgRole.'\',\'ROLE_ORGANISATIONAL\',\''.($name ? $name : $orgRole).'\',\'TRUE\',\''.$thisRole.'\');';
        $query = $this->cosi_db->query($insertQry);
        if($query){
            return true;
        }else return false;
    }

    public function updatePassword($username, $password)
    {
        $this->cosi_db->where("role_id", $username)->update("dba.tbl_authentication_built_in", array('passphrase_sha1' => sha1($password)));
        return TRUE;
    }
    
    
    private function getChildRoles($role_id, $recursive = true)
    {
    	$roles = array();
    	
    	$related_roles = $this->cosi_db->query("SELECT rr.parent_role_id, r.role_type_id
 											FROM dba.tbl_role_relations rr	
 											JOIN dba.tbl_roles r ON r.role_id = rr.parent_role_id								
 											WHERE rr.child_role_id = '" . $role_id . "'
 											  AND r.enabled='t'");
    	
    	foreach($related_roles->result() AS $row)
    	{
    		$roles[] = array("role_id" => $row->parent_role_id, "role_type_id" => $row->role_type_id);
    		if($recursive) $roles = array_merge($roles, $this->getChildRoles($row->parent_role_id));
    	}
    	
    	return $roles;
    }
    
	    
    private function getChildActivities($role_id)
    {
    	$activities = array();
    	
    	$results = $this->cosi_db->get_where("dba.tbl_role_activities", array("role_id"=>$role_id));
    	foreach($results->result() AS $row)
    	{
    		$activities[] = $row->activity_id;
    	}
    	
    	return $activities;
    }

   

}