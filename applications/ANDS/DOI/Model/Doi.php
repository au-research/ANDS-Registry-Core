<?php

namespace ANDS\DOI\Model;

use Illuminate\Database\Eloquent\Model;

class Doi extends Model
{
    /**
     * The table of the model
     * @var string
     */
    protected $table = "doi_objects";

    /**
     * The primary key of the model,
     * used for Doi::find() method
     *
     * @var string
     */
    protected $primaryKey = "doi_id";

    protected $casts = [
        'doi_id' => 'string',
    ];

    /**
     * Overload the save function
     * Update the updated_when timestamp
     *
     * @param array $options
     * @return bool|void
     */
    public function save(array $options = []) {
        $this->updated_when = date("Y-m-d H:i:s");
        return parent::save();
    }

    // don't use eloquent timestamp in favor of MySQL timestamps
    //    const CREATED_AT = 'updated_when';
    //    const UPDATED_AT = 'created_when';

    public $timestamps = false;
}