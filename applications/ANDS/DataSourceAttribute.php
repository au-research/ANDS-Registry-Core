<?php


namespace ANDS;


use Illuminate\Database\Eloquent\Model;

class DataSourceAttribute extends Model
{
    protected $table = "data_source_attributes";
    public $timestamps = false;
    protected $fillable = ['data_source_id', 'attribute', 'value'];
    protected $hidden = ['id', 'data_source_id'];
}