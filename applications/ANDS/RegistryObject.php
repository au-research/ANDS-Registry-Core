<?php


namespace ANDS;


use Illuminate\Database\Eloquent\Model;

class RegistryObject extends Model
{
    protected $table = "registry_objects";
    protected $primaryKey = "registry_object_id";
    public $timestamps = false;

    public function data()
    {
        return $this->hasMany(RecordData::class, 'registry_object_id', 'registry_object_id');
    }

    public function getCurrentData()
    {
        return RecordData::where('registry_object_id', $this->registry_object_id)
            ->where('current', "TRUE")->first();
    }

    public function setRegistryObjectAttribute($key, $value)
    {
        if ($existingAttribute = RegistryObjectAttribute::where('attribute', $key)
            ->where('registry_object_id', $this->registry_object_id)->first()
        ) {
            $existingAttribute->value = $value;
            return $existingAttribute->save();
        } else {
            return RegistryObjectAttribute::create([
                'registry_object_id' => $this->registry_object_id,
                'attribute' => $key,
                'value' => $value
            ]);
        }
    }

}