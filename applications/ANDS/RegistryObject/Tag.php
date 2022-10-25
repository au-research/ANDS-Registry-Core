<?php

namespace ANDS\RegistryObject;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    //Secret tag for allowing record to be classified as open
    public static $SECRET_TAG_ACCESS_OPEN = "accessRightsType_open";
    public static $SECRET_TAG_ACCESS_RESTRICTED = "accessRightsType_restricted";
    public static $SECRET_TAG_ACCESS_CONDITIONAL = "accessRightsType_conditional";

    public static $TAG_TYPE_PUBLIC = "public";
    public static $TAG_TYPE_SECRET = "secret";

    protected $table = "registry_object_tags";
    protected $primaryKey = "id";
    public $timestamps = false;
    public $fillable = [
        'key', 'tag', 'type', 'user', 'user_from'
    ];
}