<?php


namespace ANDS\Registry;

use ANDS\Util\StrUtil;
use Illuminate\Database\Eloquent\Model;

class IdentifierRelationshipView extends Model
{
    protected $table = "identifier_relationships";
    protected $primaryKey = null;
    public $timestamps = false;

    public function toCSV()
    {
        $url = $this->relation_url;
        if ($this->to_identifier_type == "uri" || $this->to_identifier_type == 'url') {
            $url = $this->to_identifier;
        }
        return [
            'identifier:ID' => md5($this->to_identifier),
            ':LABEL' => implode(';', ['RelatedInfo', $this->to_related_info_type]),
            'relatedInfoType' => $this->to_related_info_type,
            'identifierType' => $this->to_identifier_type,
            'identifier' => StrUtil::sanitize($this->to_identifier),
            'title' => StrUtil::sanitize($this->relation_to_title),
            'url' => StrUtil::removeNewlines($url),
            'description' => StrUtil::sanitize($this->related_description)
        ];
    }
}