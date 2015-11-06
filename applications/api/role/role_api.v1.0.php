<?php
namespace ANDS\API;

use \Exception as Exception;

class Role_api
{

    public function handle($method)
    {
        $this->ci = &get_instance();
        $this->roleDB = $this->ci->load->database('roles', true);
        $this->params = array(
            'identifier' => isset($method[1]) ? $method[1] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
        );

        try {
            $roleId = ($this->params['identifier'] ? $this->params['identifier'] : false);
            if (!$roleId) {
                $roleId = ($this->ci->input->get('roleId') ? $this->ci->input->get('roleId') : false);
            }

            if ($roleId) {
                return $this->getRole($roleId);
            }
        } catch (Exception $e) {
            return new Exception($e->getMessage());
        }
    }

    public function getRole($roleId)
    {
        $query = $this->roleDB
            ->where('role_id', $roleId)
            ->get('roles');
        if ($query->num_rows() == 0) {
            throw new Exception('No Role found for role ' . $roleId);
        }
        $role = $query->first_row();

        $include = $this->ci->input->get('include') ? $this->ci->input->get('include') : false;
        $includes = explode('-', $include);

        foreach ($includes as $inc) {
            if ($inc=='roles') {
                $role->roles = $this->getRolesAndActivitiesByRoleID($role->role_id);
            } elseif ($inc=='assoc_doi_app_id') {
                $role->roles = $this->getRolesAndActivitiesByRoleID($role->role_id);
                $role->assoc_doi_app_id = $this->getAssociatedDOIAppId($role);
            } elseif ($inc=='doi_client') {
                //for ROLE_APP_ID
                $role->client = $this->getClientForDOIAPPID($role);
            }
        }
        return $role;
    }

    private function getClientForDOIAPPID($role)
    {
        $result = array();
        $this->doiDB = $this->ci->load->database('dois', true);
        $query = $this->doiDB
            ->where('app_id', $role->role_id)
            ->get('doi_client');
        if ($query && $query->num_rows() > 0) {
            $result = $query->first_row();
        }
        return $result;
    }

    private function getAssociatedDOIAppId($role)
    {
        $result = array();
        $user_affiliations = $role->roles['organisational_roles'];
        $query = $this->roleDB
                ->distinct()->select('*')
                ->where_in('child_role_id', $user_affiliations)
                ->where('role_type_id', 'ROLE_DOI_APPID', 'after')
                ->join('roles', 'role_id = parent_role_id')
                ->from('role_relations')->get();

        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() AS $r) {
                $result[] = $r->parent_role_id;
            }
        }
        return $result;
    }

    private function getRolesAndActivitiesByRoleID($role_id, $recursive = true)
    {
        $ret = array('organisational_roles' => array(), 'functional_roles' => array(), 'activities' => array());

        $superadmin = false; // superadmins inherit all roles/functions

        $roles = $this->getChildRoles($role_id, $recursive);

        foreach ($roles as $role) {
            if (trim($role['role_type_id']) == gCOSI_AUTH_ROLE_ORGANISATIONAL) {
                $ret['organisational_roles'][] = $role['role_id'];
            } else if (trim($role['role_type_id']) == gCOSI_AUTH_ROLE_FUNCTIONAL) {
                $ret['functional_roles'][] = $role['role_id'];
                // $ret['activities'] = array_merge($ret['activities'], $this->getChildActivities($role['role_id']));

                // Check if we're a superuser
                if ($role['role_id'] == AUTH_FUNCTION_SUPERUSER) {
                    $superadmin = true;
                }
            }
        }

        // Superadmins get all organisational roles
        if ($superadmin && $recursive) {
            // function getOnlyRoleIds(&$item, $key) {$item = $item['role_id'];}
            $orgRoles = $this->getAllOrganisationalRoles();
            // array_walk($orgRoles, 'getOnlyRoleIds');
            $ret['organisational_roles'] = array_merge($ret['organisational_roles'], $orgRoles);
        }

        return $ret;
    }

    private function getChildRoles($role_id, $recursive = true, $prev = array())
    {
        $roles = array();

        $related_roles = $this->roleDB
            ->select('role_relations.parent_role_id, roles.role_type_id, roles.name, roles.role_id')
            ->from('role_relations')
            ->join('roles', 'roles.role_id = role_relations.parent_role_id')
            ->where('role_relations.child_role_id', $role_id)
            ->where('enabled', DB_TRUE)
            ->where('role_relations.parent_role_id !=', $role_id)
            ->get();

        foreach ($related_roles->result() as $row) {
            $roles[] = array("role_id" => $row->parent_role_id, "role_type_id" => $row->role_type_id);
            if ($recursive && !in_array($row->parent_role_id, $prev)) {
                array_push($prev, $row->parent_role_id);
                $child = $this->getChildRoles($row->parent_role_id, $recursive, $prev);
                if (sizeof($child) > 0) {
                    $roles = array_merge($roles, $this->getChildRoles($row->parent_role_id, $recursive, $prev));
                }
            }
        }

        return $roles;
    }

    private function getAllOrganisationalRoles()
    {
        $roles = array();
        $org_roles = $this->roleDB
            ->where('role_type_id', gCOSI_AUTH_ROLE_ORGANISATIONAL)
            ->where('enabled', DB_TRUE)
            ->order_by('name asc')
            ->get('roles');
        foreach ($org_roles->result() as $r) {
            $roles[] = $r->role_id;
        }
        return $roles;
    }

}
