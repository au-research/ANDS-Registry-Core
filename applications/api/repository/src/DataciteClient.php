<?php

namespace ANDS\API\Repository;


use Illuminate\Database\Eloquent\Model;

class DataciteClient extends Model
{
    /**
     * The table of the model
     * @var string
     */
    protected $table = "doi_client";

    /**
     * The primary key of the model,
     * used for DataciteClient::find() method
     *
     * @var string
     */
    protected $primaryKey = "client_id";
}