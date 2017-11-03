<?php

namespace ANDS\ORCID;

use Illuminate\Database\Eloquent\Model;
use ANDS\ORCID\ORCIDExport as ORCIDExport;


class ORCIDRecord extends Model
{
    protected $table = "orcid_records";
    public $incrementing = false;
    protected $primaryKey = "orcid_id";
    protected $fillable = ['orcid_id', 'record_data', 'created_at', 'updated_at'];

    public static function getTableName()
    {
        return static::table;
    }

    public function saveRecord($record_data) {
        $this->record_data = $record_data;
        $this->updated_at = time();
        $this->save();
        return $this;
    }

    public function exports() {
        return $this->hasMany(ORCIDExport::class, 'orcid_id');
    }

    public function getORCIDExports(){
        return ORCIDExport::where('orcid_id', $this->orcid_id)->get();
    }


    public static function getORCIDExportsForID($orcid_id){
        return ORCIDExport::where('orcid_id', $orcid_id)->get();
    }
}