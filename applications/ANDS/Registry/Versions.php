<?php

namespace ANDS\Registry;

use Illuminate\Database\Eloquent\Model;

class Versions extends Model
{
    protected $table = "versions";
    protected $primaryKey = "id";
    protected $fillable = ['schema_id', 'data', 'hash', 'origin', 'created_at', 'updated_at'];

    public function schema()
    {
        return $this->hasOne(Schema::class, 'id', 'schema_id');
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'origin' => $this->origin,
//            'data' => base64_encode($this->data)
        ];
    }
}