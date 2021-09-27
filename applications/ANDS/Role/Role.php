<?php


namespace ANDS\Role;


use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $connection = "roles";
    protected $table = "roles";
    protected $primaryKey = "id";

    static $ROLE_USER = 'ROLE_USER';
    static $ROLE_ORGS = 'ROLE_ORGANISATIONAL';
    static $ROLE_FUNC = 'ROLE_FUNCTIONAL';

    public function scopeUser($query)
    {
        return $query->where('role_type_id', static::$ROLE_USER);
    }

    public function scopeOrganisational($query)
    {
        return $query->where('role_type_id', static::$ROLE_ORGS);
    }

    public function scopeFunctional($query)
    {
        return $query->where('role_type_id', static::$ROLE_FUNC);
    }

    public function relations()
    {
        return $this->hasMany(RoleRelation::class, 'child_role_id', 'role_id');
    }

    public static function findByRoleID($roleID)
    {
        return static::where('role_id', $roleID)->first();
    }

    /**
     * Returns all functional role this role inherits
     *
     * @param bool $recursive
     * @return \Illuminate\Support\Collection
     */
    public function functions($recursive = true)
    {
        $this->load('relations');
        $functionalRoles =  $this->relations['relations']
            ->filter(function($relation){
                $parentRole = Role::findByRoleID($relation->parent_role_id);
                return $parentRole->role_type_id === static::$ROLE_FUNC;
            })->map(function($relation){
                return Role::findByRoleID($relation->parent_role_id);
            });

        if (!$recursive) {
            return $functionalRoles;
        }

        // find recursive roles
        $prevs = $functionalRoles->pluck('role_id')->toArray();
        foreach ($functionalRoles as $functionalRole) {
            $parentFunctions = $functionalRole->functions()->filter(function($role) use ($prevs){
                return !in_array($role->role_id, $prevs);
            })->unique();
            $functionalRoles = collect($functionalRoles)->merge($parentFunctions);
        }

        return $functionalRoles;
    }
}