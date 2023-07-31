<?php

namespace ANDS\RegistryObject;

use Illuminate\Database\Eloquent\Model;

class ThemePage extends Model
{
    protected $table = "theme_pages";
    protected $primaryKey = "id";
    public $timestamps = false;
    public $fillable = [
        'title', 'slug', 'secret_tag', 'img_src', 'description', 'visible', 'content'
    ];
}