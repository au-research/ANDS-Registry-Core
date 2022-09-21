<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

class DescriptionProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record)
    {
        $data = MetadataProvider::getSelective($record, ['recordData']);

        $descriptions = [];



        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:description') AS $description){
            $type = (string) $description['type'];
            $description = utf8_encode(html_entity_decode((string) $description));
            $descriptions[] = [
                'type' => $type,
                'value' => $description
            ];
        }

        $shortDescription = collect($descriptions)->filter(function($item) {
            return $item['type'] == "brief";
        })->first();

        $fullDescription = collect($descriptions)->filter(function($item) {
            return $item['type'] == "full";
        })->first();

        // primary description is the best found description, without tags, suitable for use in XML
        $primaryDescription = $shortDescription ?: null;
        if (!$primaryDescription && $fullDescription) {
            $primaryDescription = $fullDescription;
        }
        if ($primaryDescription) {
            $primaryDescription['value'] = strip_tags($primaryDescription['value']);
        }

        return [
            'primary_description' => $primaryDescription ? $primaryDescription['value'] : null,
            'brief' => $shortDescription ? $shortDescription['value'] : null,
            'full' => $fullDescription ? $fullDescription['value'] : null,
            // 'logo' => null TODO logo
            'descriptions' => $descriptions
        ];
    }

    /**
     * Obtain an associative array for the indexable fields
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record)
    {

        $types = [];
        $values = [];
        $solr_byte_limit = 32766;
        $descriptions = self::get($record);
        foreach ($descriptions['descriptions'] as $description) {
            $types[] = $description['type'];
            $values[] = mb_strcut($description['value'], 0, $solr_byte_limit);
        }

        $theDescription = mb_strcut($descriptions['primary_description'],0, $solr_byte_limit);;

        // list description is the trimmed form of the description
        $listDescription = mb_strcut(trim(strip_tags(html_entity_decode(html_entity_decode($theDescription)), ENT_QUOTES)),0, $solr_byte_limit);;

        //add <br/> for NL if doesn't already have <p> or <br/>
        $theDescription = !(strpos($theDescription, "&lt;br") !== FALSE || strpos($theDescription, "&lt;p") !== FALSE || strpos($theDescription, "&amp;#60;p") !== FALSE) ? nl2br($theDescription) : $theDescription;

        return [
            'description_type' => $types,
            'description_value' => $values,
            'list_description' => $listDescription,
            'description' => $theDescription
        ];

    }
}