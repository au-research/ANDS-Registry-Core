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
            $description = html_entity_decode((string) $description);
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
}