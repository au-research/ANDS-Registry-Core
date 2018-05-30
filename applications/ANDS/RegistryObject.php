<?php


namespace ANDS;


use ANDS\RegistryObject\ExportToCSVTrait;
use ANDS\RegistryObject\Identifier;
use ANDS\RegistryObject\Metadata;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\XMLUtil;
use Illuminate\Database\Eloquent\Model;

class RegistryObject extends Model
{
    /** traits */
    use ExportToCSVTrait;

    protected $table = "registry_objects";
    protected $primaryKey = "registry_object_id";
    public $timestamps = false;
    public $duplicateRecordIds = null;
    public $identifiers = null;

    public static $classes = ['collection', 'service', 'party', 'activity'];
    public static $statuses = [
        "MORE_WORK_REQUIRED",
        "DRAFT",
        "SUBMITTED_FOR_ASSESSMENT",
        "ASSESSMENT_IN_PROGRESS",
        "APPROVED",
        "PUBLISHED"
    ];
    public static $levels = [1,2,3,4];

    /** @var string */
    protected static $STATUS_PUBLISHED = 'PUBLISHED';

    protected $fillable = ['key', 'title', 'status', 'group', 'data_source_id'];


    /**
     * Eloquent Accessor
     * usage: $this->id will return $this->registry_object_id
     *
     * @return mixed
     */
    public function getIdAttribute()
    {
        return $this->registry_object_id;
    }

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

    public function datasource()
    {
        return $this->belongsTo(DataSource::class, 'data_source_id', 'data_source_id');
    }

    public function registryObjectAttributes()
    {
        return $this->hasMany(RegistryObjectAttribute::class, 'registry_object_id', 'registry_object_id');
    }

    /**
     * Eloquent
     * Returns a current recordData
     *
     * @return RecordData
     */
    public function getCurrentData()
    {
        $currentData = RecordData::where('registry_object_id', $this->registry_object_id)
            ->where('scheme', "rif")->where('current', 'TRUE')->first();

        // get the last rif
        if ($currentData === null) {
            $currentData = RecordData::where('registry_object_id', $this->registry_object_id)
                ->where('scheme', "rif")->orderBy('timestamp', 'desc')->first();
        }

        return $currentData;
    }

    public function recordData()
    {
        return $this->hasMany(RecordData::class, 'registry_object_id', 'registry_object_id');
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
        $this->findAllDuplicates();

        return RegistryObject::whereIn('registry_object_id', $this->duplicateRecordIds)
            ->where('status', 'PUBLISHED')
            ->get();
    }

    public function findAllDuplicates(){

        if(is_array($this->duplicateRecordIds)){
            return $this->duplicateRecordIds;
        }
        
        $this->identifiers = Identifier::where('registry_object_id', $this->registry_object_id)->get()->pluck('identifier')->toArray();

        $recordIDs = Identifier::whereIn('identifier', $this->identifiers)->get()->pluck('registry_object_id')->unique()->filter(function($item){
            return $item != $this->registry_object_id;
        })->toArray();



        $this->duplicateRecordIds = $recordIDs;

        while(count($recordIDs) > 0)
        {
            $moreIdentifiers = Identifier::whereIn('registry_object_id', $this->duplicateRecordIds)->get()->pluck('identifier')->unique()->filter(function($item){
                return !in_array($item, $this->identifiers);
            })->toArray();

            if($moreIdentifiers){
                $recordIDs = Identifier::whereIn('identifier', $moreIdentifiers)->get()->pluck('registry_object_id')->unique()->filter(function($item){
                    return !in_array($item ,$this->duplicateRecordIds);
                })->toArray();
                $this->duplicateRecordIds = array_merge($this->duplicateRecordIds, $recordIDs);
            }else{
                $recordIDs = [];
            }

        }
        
        return $this->duplicateRecordIds;
        
    }


    /**
     * $this->portalUrl
     * $this->portal_url
     * @return string
     */
    public function getPortalUrlAttribute()
    {
        return baseUrl($this->slug.'/'.$this->registry_object_id);
    }

    /**
     * $this->portalUrlWithKey
     * @return string
     */
    public function getPortalUrlWithKeyAttribute()
    {
        return baseUrl("view/?key={$this->key}");
    }
}