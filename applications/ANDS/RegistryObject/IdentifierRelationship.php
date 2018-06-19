<?php


namespace ANDS\RegistryObject;


use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\StrUtil;
use Illuminate\Database\Eloquent\Model;

class IdentifierRelationship extends Model
{
    protected $table = "registry_object_identifier_relationships";
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'registry_object_id',
        'related_object_identifier',
        'related_info_type',
        'related_object_identifier_type',
        'relation_type',
        'related_title',
        'related_url',
        'related_description',
        'connections_preview_div',
        'notes'
    ];

    /**
     * $relationship->resolvesToRecord
     *
     * @return boolean
     */
    public function getResolvesToRecordAttribute()
    {
        $record = $this->getToRecord();
        if (!$record) {
            return false;
        }

        return true;
    }

    /**
     * get the record that this relations relates to
     * null if not exists
     *
     * @return \ANDS\RegistryObject|null
     */
    public function getToRecord()
    {
        $id = Identifier::where('identifier', $this->related_object_identifier)->pluck('registry_object_id')->first();
        return RegistryObjectsRepository::getRecordByID($id);
    }

    public function toCSV()
    {
        $url = $this->related_url;

        if (!$url && $this->related_info_type == 'website') {
            $url = $this->related_object_identifier;
        }

        return [
            'identifier:ID' => $this->related_object_identifier,
            ':LABEL' => implode(';', ['RelatedInfo', $this->related_info_type]),
            'relatedInfoType' => $this->related_info_type,
            'type' => $this->related_object_identifier_type,
            'title' => StrUtil::sanitize($this->related_title),
            'url' => $url,
            'description' => $this->related_description
        ];
    }
}

