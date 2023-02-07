<?php
namespace ANDS\Registry\ContentProvider\ANZCTR;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Registry\Schema;
use ANDS\Registry\Versions as Versions;
use ANDS\RegistryObject;
use ANDS\RegistryObject\RegistryObjectVersion;
use ANDS\Util\ANZCTRUtil;
use DOMDocument;

class ContentProvider{




    /**
     * @return
     */
    public static function getIndexableArray(RegistryObject $record)
    {
        $relatedIdentifiers = IdentifierProvider::getRelatedInfoIdentifiers($record);
        foreach ($relatedIdentifiers as $relatedIdentifier){
            if(str_contains($relatedIdentifier['value'], 'ACTRN=')) {
                $arr = explode('ACTRN=', $relatedIdentifier['value']);
                $content = ANZCTRUtil::retrieveMetadata('ACTRN'.$arr[1]);
                ContentProvider::storeACTRNMetadata($record,$content);
                $dom = new DOMDocument;
                $dom->loadXML($content);
                return ContentProvider::getIndex($dom, $relatedIdentifier['value'], $arr[1]);
            }
        }
        return [];
    }


    /*
     *
     *
     Searchable ANZCTR metadata fields:
    Public Title (publictitle)
    Brief Summary (briefsummary)
    Conditions (healthcondition)
    Condition Codes (conditioncode1, conditioncode2)
    Study Type (studytype)
    Ethics Approval (ethicsapproval)
    Inclusive Criteria (inclusivecriteria)
    Intervention Codes (interventioncode)


    Visiable ANZCTR metadata fields in the trial preview window:

    Public Title (publictitle)
    Brief Summary (briefsummary) - Needs a maximum length limitation
    Conditions (healthcondition)
    Condition Codes (conditioncode1, conditioncode2)
    Study Type (studytype)
    Ethics Approval (ethicsapproval)
     *
     *
     */


    public static function getIndex(DOMDocument $dom, $url, $identifier){
        return [
            'anzctr_identifier' => $identifier,
            'anzctr_url' => $url,
            'anzctr_publictitle' => ContentProvider::getFirst($dom, array('publictitle')),
            'anzctr_briefsummary' => ContentProvider::getFirst($dom, array('briefsummary')),
            'anzctr_conditions' => ContentProvider::getContent($dom, array('healthcondition')),
            'anzctr_conditioncodes' => ContentProvider::getContent($dom, array('conditioncode1','conditioncode2')),
            'anzctr_studytype' => ContentProvider::getContent($dom, array('studytype')),
            'anzctr_ethicsapproval' => ContentProvider::getContent($dom, array('ethicsapproval')),
            'anzctr_inclusivecriteria' => ContentProvider::getContent($dom, array('inclusivecriteria')),
            'anzctr_interventioncode' => ContentProvider::getContent($dom, array('interventioncode')),
            'anzctr_text' => ContentProvider::getContent($dom, array('publictitle', 'briefsummary', 'healthcondition', 'conditioncode1', 'conditioncode2', 'inclusivecriteria'))
        ];
    }

    public static function getContent($dom, $elements){
        $indexableArray = [];
         foreach ($elements as $el) {
             $element = $dom->getElementsByTagName($el);
             foreach ($element as $e) {
                 $content = [];
                 foreach ($e->childNodes as $node) {
                         $content[] = $node->nodeValue;
                 }
                 $indexableArray[] = implode(', ', $content);
             }
         }
         return $indexableArray;
    }


    public static function getFirst($dom, $elements){

        foreach ($elements as $el) {
            $element = $dom->getElementsByTagName($el);
            foreach ($element as $e) {
                return $e->nodeValue;
            }
        }
        return "";
    }




    private static function storeACTRNMetadata($record, $data){
        $schema = Schema::get("https://anzctr_org.au");
        $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $record->id)->get()->pluck('version_id')->toArray();
        $existing = null;
        if (count($altVersionsIDs) > 0) {
            $existing = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $schema->id)->first();
        }
        $success = true;
        $hash = md5($data);
        if (!$existing) {
            $version = Versions::create([
                'data' => $data,
                'hash' => $hash,
                'origin' => 'HARVESTER',
                'schema_id' => $schema->id,
            ]);
            RegistryObjectVersion::firstOrCreate([
                'version_id' => $version->id,
                'registry_object_id' => $record->id
            ]);
        } elseif ($hash != $existing->hash) {
            $existing->update([
                'data' => $data,
                'hash' => $hash
            ]);
        }
    }

}