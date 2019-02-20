<?php

namespace ANDS\Registry;

use Illuminate\Database\Eloquent\Model;

class Schema extends Model
{
    protected $table = "schemas";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = ['prefix', 'uri', 'exportable'];


    public static function getPrefix($schemaURI){
        $prefixMap = explode('/', $schemaURI);
        $bigNum = 1000;
        $prefixSize = 3;
        $prefix = '';
        $counter = 0;
        foreach($prefixMap as $item){
            $counter--;
            if(str_contains($item , 'iso')){
                $prefix = 'iso';
                $counter = $bigNum;
            }
            elseif($item == 'iso'){ //reset if found only iso
                $prefix = $item;
                $counter = $bigNum;
            }
            elseif($counter > $bigNum - $prefixSize){
                $prefix .= $item;
            }
        }
        if(strlen(trim($prefix)) == 0)
            return $schemaURI;

        return $prefix;

    }
}