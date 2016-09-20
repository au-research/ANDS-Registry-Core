<?php


namespace ANDS;


use ANDS\RegistryObject\Metadata;
use ANDS\Repository\RegistryObjectsRepository;
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

    public function getRegistryObjectAttribute($key)
    {
        return RegistryObjectAttribute::where('registry_object_id', $this->registry_object_id)
            ->where('attribute', $key)->first();
    }

    public function getRegistryObjectAttributeValue($key)
    {
        $attribute = $this->getRegistryObjectAttribute($key);
        return $attribute->value;
    }

    public function getRegistryObjectMetadata($key)
    {
        return Metadata::where('registry_object_id', $this->registry_object_id)
            ->where('attribute', $key)->first();
    }

    public function isPublishedStatus()
    {
        return RegistryObjectsRepository::isPublishedStatus($this->status);
    }

    public function isDraftStatus()
    {
        return RegistryObjectsRepository::isDraftStatus($this->status);
    }

}