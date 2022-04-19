<?php

namespace ANDS\DOI\Model;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $primaryKey = "activity_id";
    protected $table = "activity_log";
    public $timestamps = false;
    protected $fillable = ["activity", "doi_id", "result", "client_id", "message"];
}