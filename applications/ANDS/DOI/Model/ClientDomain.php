<?php

namespace ANDS\DOI\Model;


use Illuminate\Database\Eloquent\Model;

class ClientDomain extends Model
{
    protected $table = "doi_client_domains";
    protected $primaryKey = "clientdomainid";
    protected $fillable = ['client_domain'];
    public $timestamps = false;
}