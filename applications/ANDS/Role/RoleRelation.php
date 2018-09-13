<?php


namespace ANDS\Role;


use Illuminate\Database\Eloquent\Model;

class RoleRelation extends Model
{
    protected $connection = "roles";
    protected $primaryKey = "id";
}