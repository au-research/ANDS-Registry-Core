<?php


namespace ANDS;


use Illuminate\Database\Eloquent\Model;

class RecordData extends Model
{
    protected $table = "record_data";
    protected $primaryKey = "id";
    public $timestamps = false;

    protected $fillable = ['registry_object_id', 'current', 'data', 'timestamp'];

    public static function getTableName()
    {
        return static::$table;
    }

    public function saveData($data, $scheme = "rif") {
        $this->scheme = "rif";
        $this->hash = md5($data);
        $this->data = $data;
        return $this;
    }
    
}