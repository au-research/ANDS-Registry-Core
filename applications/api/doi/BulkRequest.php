<?php


namespace ANDS\API\DOI;


use Illuminate\Database\Eloquent\Model;

class BulkRequest extends Model
{
    protected $table = 'bulk_requests';
    public $timestamps = false;
}