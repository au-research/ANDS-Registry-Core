<?php

namespace ANDS\DataSource;

use Illuminate\Database\Eloquent\Model;

class DataSourceLog extends Model
{
    protected $table = "data_source_logs";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = ['data_source_id', 'type', 'log', 'class', 'date_modified'];
}