<?php

namespace ANDS\DataSource;

use Illuminate\Database\Eloquent\Model;

class Harvest extends Model
{
    protected $table = "harvests";
    protected $primaryKey = "harvest_id";
    public $timestamps = false;
    protected $fillable = ['data_source_id', 'status', 'message', 'next_run', 'last_run', 'mode', 'batch_number', 'importer_message'];

    public function getMessage(){
        return json_encode($this->message);
    }

    public function reschedule()
    {
        $this->status = "SCHEDULED";
        return $this;
    }

    public function stop()
    {
        $this->status = "STOPPED";
        return $this;
    }

}