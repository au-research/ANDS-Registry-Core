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

/**
 * Base Model for Roles Management
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Roles extends CI_Model {

	private $cosi_db = null;

    function __construct(){
        parent::__construct();
		$this->cosi_db = $this->load->database('roles', TRUE);
    }

    /**
     * function to return all of the available roles, enabled or not
     * @return array_object
     */
    function all_roles(){
        $result = $this->cosi_db->order_by('name','asc')->get("roles");
        return $result->result();
    }

    /**
     * returns a list of role based on the role_type_id
     * @param  string $role_type_id if not provided, return all roles
     * @return array_object
     */
    function list_roles($role_type_id){
        if($role_type_id){
            $result = $this->cosi_db->get_where("roles",
                                                    array(
                                                        "role_type_id"=>$role_type_id
                                                    ));
        }else{
            $result = $this->cosi_db->get("roles");
        }
        return $result->result();
    }

    /**
     * retrieve a single role
     * @param  string $role_id the role_id identifier
     * @return object
     */
    function get_role($role_id){
        $result = $this->cosi_db->get_where("roles",
                                                    array(
                                                        "role_id"=>$role_id
                                                    ));
        foreach($result->result() as $r){
            return $r;
        }
    }

    /**
     * add a relation between roles, this adds an entry into the role_relations table
     * @param string $parent_role_id
     * @param string $child_role_id
     */
    function add_relation($parent_role_id, $child_role_id){
        $result = $this->cosi_db->insert('role_relations',
            array(
                'parent_role_id'=>$parent_role_id,
                'child_role_id'=>$child_role_id,
                'created_who'=>$this->user->localIdentifier()
            )
        );
        return $result;
    }

    /**
     * this function remove a relation between 2 roles, explicit parent and child must be provided
     * @param  string $parent_role_id
     * @param  string $child_role_id
     * @return result
     */
    function remove_relation($parent_role_id, $child_role_id){
        $result = $this->cosi_db->delete('role_relations',
            array(
                'parent_role_id'=>$parent_role_id,
                'child_role_id'=>$child_role_id
            )
        );
        return $result;
    }

    /**
     * recursive function that goes through and collect all of the (parents) of a role
     * @param  string $role_id
     * @return array_object if an object has a child, object->childs will be a list of the child objects
     */
    function list_childs($role_id, $include_doi=false, $prev=array()){
        $res = array();
        // $role = $this->get_role($role_id);
        // return $res;


        $result = $this->cosi_db
                ->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
                ->from('role_relations')
                ->join('roles', 'roles.role_id = role_relations.parent_role_id')
                ->where('role_relations.child_role_id', $role_id)
                ->where('enabled', DB_TRUE)
                ->where('role_relations.parent_role_id !=', $role_id)
                ->get();

        if($result->num_rows() > 0){
            foreach($result->result() as $r){
                if(trim($r->role_type_id)=='ROLE_DOI_APPID' && $include_doi){
                    $res[] = $r;
                }else if(!$include_doi){
                    $res[] = $r;
                }
                if(!in_array($r->role_id, $prev)) {
                    array_push($prev, $r->role_id);
                    $childs = $this->list_childs($r->parent_role_id, $include_doi, $prev);
                    if(sizeof($childs) > 0){
                        $r->childs = $childs;
                    }else{
                        $r->childs = false;
                    }
                }
            }
        }
        return $res;
    }

    function immediate_childs($role_id) {
        $res = array();
        $result = $this->cosi_db
                ->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
                ->from('role_relations')
                ->join('roles', 'roles.role_id = role_relations.parent_role_id')
                ->where('role_relations.child_role_id', $role_id)
                ->where('enabled', DB_TRUE)
                ->where('role_relations.parent_role_id !=', $role_id)
                ->get();
        if($result->num_rows() > 0){
            foreach($result->result() as $r){
                $res[] = $r;
            }
        }
        return $res;
    }

    function immediate_parents($role_id) {
         $res = array();
        $result = $this->cosi_db
                ->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
                ->from('role_relations')
                ->join('roles', 'roles.role_id = role_relations.parent_role_id')
                ->where('role_relations.parent_role_id', $role_id)
                ->where('enabled', DB_TRUE)
                ->where('role_relations.child_role_id !=', $role_id)
                ->get();
        if($result->num_rows() > 0){
            foreach($result->result() as $r){
                $res[] = $r;
            }
        }
        return $res;
    }

    /**
     * basically reverse of the list_childs function, search for all (childs) of a role
     * @param  string $role_id
     * @return array_object
     */
    function descendants($role_id, $include_doi=false, $prev = array()){
        $res = array();

        $result = $this->cosi_db
                    ->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
                    ->from('role_relations')
                    ->join('roles', 'roles.role_id = role_relations.child_role_id')
                    ->where('role_relations.parent_role_id', $role_id)
                    ->where('enabled', DB_TRUE)
                    ->where('role_relations.child_role_id !=', $role_id)
                    ->get();

        if($result->num_rows() > 0){
            foreach($result->result() as $r){
                if(trim($r->role_type_id)=='ROLE_DOI_APPID' && $include_doi){
                    $res[] = $r;
                }else if(!$include_doi){
                    $res[] = $r;
                }

                if(!in_array($r->role_id, $prev)){
                    array_push($prev, $r->role_id);
                    $childs = $this->descendants($r->role_id, $include_doi, $prev);
                    if(sizeof($childs) > 0){
                        $r->childs = $childs;
                    }else $r->childs = false;
                }
            }
        }
        return $res;
    }

    /**
     * getting all the missing descendants for organisational view
     * @param  string $role_id
     * @param  array $descendants
     * @return array_object
     */
    function missing_descendants($role_id, $descendants, $include_doi=false){
        $ownedRoles = array();
        // $descendants = new RecursiveIteratorIterator(new RecursiveArrayIterator($descendants));
        foreach($descendants as $d) $ownedRoles[] = $d->role_id;
        $this->cosi_db->select('role_id, name, role_type_id')->from('roles')->where('role_type_id', 'ROLE_USER');
        if($include_doi) $this->cosi_db->or_where('role_type_id', 'ROLE_DOI_APPID');
        if(sizeof($ownedRoles) > 0) $this->cosi_db->where_not_in('role_id', $ownedRoles);
        $result = $this->cosi_db->get();

        $res = array();
        foreach($result->result() as $r) {
            if($include_doi && trim($r->role_type_id)=='ROLE_DOI_APPID'){
                $res[] = $r;
            }else if(!$include_doi){
                $res[] = $r;
            }
        }
        return $res;
    }

    /**
     * use a service provided by the registry to find out all the data sources affiliated with an org role
     * @param  string $org_role
     * @return array
     */
    function get_datasources($org_role){
        $url = $this->config->item('registry_endpoint') .'get_datasources/?record_owner='.rawurlencode($org_role);
        $contents = json_decode(@file_get_contents($url),true);
        return $contents;
    }

    /**
     * getting the missing functional and org role that a role has
     * missing functional role is recursive based on the getRolesAndActivityByRoleID function based in the role_authentication model
     * missing org role is based on the owned org role (separate query)
     * @param  string $role_id the role to be queried on
     * @return array         ['functional']['organisational']
     */
    function get_missing($role_id){
        $res = array('functional'=>array(), 'organisational'=>array());

        $this->load->model($this->config->item('authentication_class'), 'auth');
        $recursiveRoles = $this->auth->getRolesAndActivitiesByRoleID($role_id);

        //missing functional roles black magic
        $this->cosi_db->select('role_id, name, role_type_id')->from('roles')->where('role_type_id', 'ROLE_FUNCTIONAL');
        if(sizeof($recursiveRoles['functional_roles']) > 0) $this->cosi_db->where_not_in('role_id', $recursiveRoles['functional_roles']);
        $result = $this->cosi_db->get();


        foreach($result->result() as $r) $res['functional'][] = $r;

        //missing org roles black magic
        $ownedOrgRole = $this->cosi_db
                ->select('parent_role_id')
                ->from('role_relations')
                ->join('roles', 'roles.role_id = role_relations.parent_role_id')
                ->where('role_relations.child_role_id', $role_id)
                ->where('roles.role_type_id', 'ROLE_ORGANISATIONAL')
                ->where('enabled', DB_TRUE)
                ->where('role_relations.parent_role_id !=', $role_id)
                ->get();
        $owned = array();
        foreach($ownedOrgRole->result() as $o) $owned[] = $o->parent_role_id;
        $this->cosi_db->select('role_id, name, role_type_id')->from('roles')->where('role_type_id', 'ROLE_ORGANISATIONAL');
        if(sizeof($owned)>0) $this->cosi_db->where_not_in('role_id', $owned);
        $result = $this->cosi_db->get();
        foreach($result->result() as $r) $res['organisational'][] = $r;

        return $res;
    }

    /**
     * Register a Role in the roles table, if the role has authentication built in, then create another entry in the relevant table
     * @param [type] $post [description]
     */
    function add_role($post){
        $query = $this->cosi_db->get_where('roles', array('role_id'=>$post['role_id']));
        if($query->num_rows() > 0) throw new Exception('Role ID '.$post['role_id'].' already exists');
        $add = $this->cosi_db->insert('roles',
            array(
                'role_id'=>$post['role_id'],
                'name'=>$post['name'],
                'role_type_id'=>$post['role_type_id'],
                'enabled'=> ($post['enabled']=='1' ? DB_TRUE : DB_FALSE),
                'created_when' => date('Y-m-d H:i:s',time()),
                'authentication_service_id'=> trim($post['authentication_service_id']),
                'created_who'=>$this->user->localIdentifier()
            )
        );
        if($post['authentication_service_id']=='AUTHENTICATION_BUILT_IN' && $post['role_type_id']=='ROLE_USER'){
            $this->cosi_db->insert('authentication_built_in',
                array(
                    'role_id'=>$post['role_id'],
                    'passphrase_sha1'=>sha1('abc123'),
                    'created_who'=>$this->user->localIdentifier(),
                    'modified_who'=>$this->user->localIdentifier()
                )
            );
        }
        $this->user->refreshAffiliations($this->user->localIdentifier());
    }

    /**
     * Update a role name and enable status
     * @param  string $role_id
     * @param  array $post
     * @return true
     */
    function edit_role($role_id, $post){
        $this->cosi_db->where('role_id', $role_id);
        $this->cosi_db->update('roles',
            array(
                'name'=> $post['name'],
                'enabled'=>($post['enabled']=='1' ? DB_TRUE : DB_FALSE)
            )
        );
    }

    function reset_built_in_passphrase($role_id)
    {
        $this->cosi_db->where('role_id', $role_id);
        $this->cosi_db->update('authentication_built_in', array('passphrase_sha1'=>sha1('abc123')));
    }

    /**
     * Remove a role and all relations associated with it
     * @param  string $role_id
     * @return true
     */
    function delete_role($role_id){
        $this->cosi_db->delete('role_relations', array('parent_role_id' => $role_id));
        $this->cosi_db->delete('role_relations', array('child_role_id' => $role_id));
        $this->cosi_db->delete('roles', array('role_id' => $role_id));
    }

    /**
     * Migrate from old PGSQL COSI database to the new one, recommended to run before the usage of roles management
     * @return true
     */
    function migrate_from_cosi(){
        $this->load->dbforge();

        $this->old_cosi_db = $this->load->database('cosi', true);
        $this->new_cosi_db = $this->load->database('roles', true);
        $this->dbforge->set_database($this->new_cosi_db);

        //Removes the existing table
        $this->dbforge->drop_table('roles');
        $this->dbforge->drop_table('role_relations');
        $this->dbforge->drop_table('authentication_built_in');

        //roles table schema
        $fields = array(
            'role_id' => array(
                'type'=>'VARCHAR','constraint'=> 255
            ),
            'role_type_id'=>array(
                'type'=>'VARCHAR','constraint'=> 20
            ),
            'name'=>array(
                'type'=>'VARCHAR','constraint'=> 255
            ),
            'authentication_service_id'=>array(
                'type'=>'VARCHAR','constraint'=> 32
            ),
            'enabled'=>array(
                'type'=>'VARCHAR', 'constraint'=>1, 'default'=>DB_TRUE
            ),
            'created_when'=>array(
                'type'=>'timestamp'
            ),
            'created_who'=>array(
                'type'=>'VARCHAR', 'constraint'=>255,'default'=>'SYSTEM'
            ),
            'modified_who'=>array(
                'type'=>'VARCHAR', 'constraint'=>255,'default'=>'SYSTEM'
            ),
            'modified_when'=>array(
                'type'=>'timestamp'
            ),
            'last_login'=>array(
                'type'=>'timestamp'
            ),
        );
        $this->dbforge->add_field('id');
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('role_id', true);
        $this->dbforge->create_table('roles', true);

        $all_roles = $this->old_cosi_db->get('dba.tbl_roles');
        foreach($all_roles->result() as $r){
            $this->db->insert('roles',
                array(
                    'role_id'=>$r->role_id,
                    'role_type_id'=>$r->role_type_id,
                    'name'=>$r->name,
                    'authentication_service_id'=>($r->authentication_service_id ? trim($r->authentication_service_id) : ''),
                    'enabled'=>($r->enabled==DB_TRUE ? DB_TRUE: DB_FALSE),
                    'created_when'=>$r->created_when,
                    'created_who'=>$r->created_who,
                    'modified_who'=>$r->modified_who,
                    'modified_when'=>$r->modified_when,
                    'last_login'=>$r->last_login
                )
            );
        }

        //role relations
        $fields = array(
            'parent_role_id'=>array(
                'type'=>'VARCHAR','constraint'=>255
            ),
            'child_role_id'=>array(
                'type'=>'VARCHAR','constraint'=>255
            ),
            'created_who'=>array(
                'type'=>'VARCHAR','constraint'=>255,'default'=>'SYSTEM'
            ),
            'created_when'=>array(
                'type'=>'timestamp'
            ),
            'modified_when'=>array(
                'type'=>'timestamp'
            ),
            'modified_who'=>array(
                'type'=>'VARCHAR','constraint'=>255,'default'=>'SYSTEM'
            ),
        );
        $this->dbforge->add_field('id');
        $this->dbforge->add_field($fields);
        $this->dbforge->create_table('role_relations', true);
        $all_relations = $this->old_cosi_db->get('dba.tbl_role_relations');
        foreach($all_relations->result() as $r){
            $this->db->insert('role_relations',
                array(
                    'parent_role_id'=>$r->parent_role_id,
                    'child_role_id'=>$r->child_role_id,
                    'created_who'=>$r->created_who,
                    'created_when'=>$r->created_when,
                    'modified_when'=>$r->modified_when,
                    'modified_who'=>$r->modified_who
                )
            );
        }

        //authentication_built_in
        $fields = array(
            'role_id'=>array(
                'type'=>'VARCHAR', 'constraint'=>255
            ),
            'passphrase_sha1'=>array(
                'type'=>'VARCHAR', 'constraint'=>40
            ),
            'created_who'=>array(
                'type'=>'VARCHAR','constraint'=>255,'default'=>'SYSTEM'
            ),
            'created_when'=>array(
                'type'=>'timestamp'
            ),
            'modified_when'=>array(
                'type'=>'timestamp'
            ),
            'modified_who'=>array(
                'type'=>'VARCHAR','constraint'=>255,'default'=>'SYSTEM'
            )
        );
        $this->dbforge->add_field('id');
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('role_id', true);
        $this->dbforge->create_table('authentication_built_in', true);
        $all_built_in = $this->old_cosi_db->get('dba.tbl_authentication_built_in');
        foreach($all_built_in->result() as $r){
            $this->db->insert('authentication_built_in',
                array(
                    'role_id'=>$r->role_id,
                    'passphrase_sha1'=>$r->passphrase_sha1,
                    'created_who'=>$r->created_who,
                    'created_when'=>$r->created_when,
                    'modified_when'=>$r->modified_when,
                    'modified_who'=>$r->modified_who
                )
            );
        }

    }
}