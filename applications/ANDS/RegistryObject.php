<?php


namespace ANDS;


use ANDS\RegistryObject\Identifier;
use ANDS\RegistryObject\Metadata;
use ANDS\Repository\RegistryObjectsRepository;
use Illuminate\Database\Eloquent\Model;

class RegistryObject extends Model
{
    protected $table = "registry_objects";
    protected $primaryKey = "registry_object_id";
    public $timestamps = false;

    /**
     * Eloquent
     * Returns all record_data that ties to this
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data()
    {
        return $this->hasMany(RecordData::class, 'registry_object_id', 'registry_object_id');
    }

    /**
     * Eloquent
     * Returns a current recordData
     *
     * @return RecordData
     */
    public function getCurrentData()
    {
        return RecordData::where('registry_object_id', $this->registry_object_id)
            ->where('current', "TRUE")->first();
    }

    /**
     * Creates registryObjectAttribute if not exists
     * Updates existing registryObjectAttribute
     *
     * @param $key
     * @param $value
     * @return static
     */
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

    public function deleteRegistryObjectAttribute($key){
        RegistryObjectAttribute::where('attribute', $key)
            ->where('registry_object_id', $this->registry_object_id)->delete();
    }
    
    /**
     * Get a RegistryObjectAttribute of this by key
     *
     * @param $key
     * @return RegistryObjectAttribute
     */
    public function getRegistryObjectAttribute($key)
    {
        return RegistryObjectAttribute::where('registry_object_id', $this->registry_object_id)
            ->where('attribute', $key)->first();
    }

    /**
     * Just get the value of an attribute by key
     *
     * @param $key
     * @return null|mixed
     */
    public function getRegistryObjectAttributeValue($key)
    {
        $attribute = $this->getRegistryObjectAttribute($key);
        if ($attribute !== null) {
            return $attribute->value;
        }
        return null;
    }

    /**
     * Get RegistryObjectMetadata by key
     *
     * @param $key
     * @return mixed
     */
    public function getRegistryObjectMetadata($key)
    {
        return Metadata::where('registry_object_id', $this->registry_object_id)
            ->where('attribute', $key)->first();
    }

    /**
     * Delete Registry Object Metadata
     * @param $key
     * @return mixed
     */
    public function deleteRegistryObjectMetadata($key)
    {
        return Metadata::where('registry_object_id', $this->registry_object_id)
            ->where('attribute', $key)->delete();
    }

    /**
     * set RegistryObjectMetadata value
     *
     * @param $key
     * @param $value
     * @return Metadata
     */
    public function setRegistryObjectMetadata($key, $value)
    {
        if ($existingMetadata = Metadata::where('registry_object_id', $this->registry_object_id)
            ->where('attribute', $key)->first()
        ) {
            $existingMetadata->value = $value;
            return $existingMetadata->save();
        } else {
            return Metadata::create([
                'registry_object_id' => $this->registry_object_id,
                'attribute' => $key,
                'value' => $value
            ]);
        }
    }
    
    /**
     * is this of published status
     * @return bool
     */
    public function isPublishedStatus()
    {
        return RegistryObjectsRepository::isPublishedStatus($this->status);
    }

    /**
     * is this of draft status
     *
     * @return bool
     */
    public function isDraftStatus()
    {
        return RegistryObjectsRepository::isDraftStatus($this->status);
    }

    /**
     * is this manually entered
     *
     * @return bool
     */
    public function isManualEntered()
    {
        return strpos($this->getRegistryObjectAttributeValue('harvest_id') ,"MANUAL-") === 0;
    }


    /**
     * does this have a certain harvest_id?
     *
     * @param $harvestID
     * @return bool
     */
    public function hasHarvestID($harvestID)
    {
        if ($this->getRegistryObjectAttributeValue('harvest_id') == $harvestID) {
            return true;
        }
        return false;
    }

    /**
     * does this has a different harvest_id than
     *
     * @param $harvestID
     * @param bool $excludeManualEntered
     * @return bool
     */
    public function hasDifferentHarvestID($harvestID, $excludeManualEntered = true)
    {
        if ($this->getRegistryObjectAttributeValue('harvest_id') == $harvestID) {
            return false;
        }
        if ($excludeManualEntered && $this->isManualEntered()) {
            return false;
        }
        return true;
    }

    public function getDuplicateRecords()
    {
        // via identifier
        $identifiers = Identifier::where('registry_object_id', $this->registry_object_id);

        $recordIDs = Identifier::whereIn(
            'identifier', $identifiers->get()->pluck('identifier')
        )->get()->pluck('registry_object_id')->unique()->filter(function($item){
            return $item != $this->registry_object_id;
        });

        return RegistryObject::whereIn('registry_object_id', $recordIDs)
            ->where('status', 'PUBLISHED')
            ->get();
    }

    public function hasRelatedClass($class){
        return true;
    }
    
}