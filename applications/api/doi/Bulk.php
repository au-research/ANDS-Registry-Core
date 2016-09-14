<?php


namespace ANDS\API\DOI;


use Illuminate\Database\Eloquent\Model;

class Bulk extends Model
{
    protected $table = 'bulk';
    public $timestamps = false;
    protected $fillable = ['doi', 'target', 'from', 'to'];
    protected $attributes = [
        'status' => 'PENDING'
    ];
}