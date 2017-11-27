<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use Carbon\Carbon;

class CitationProvider implements RIFCSProvider
{

    public static function process(RegistryObject $record)
    {
        // TODO: Implement process() method.
    }

    public static function get(RegistryObject $record)
    {
        $data = MetadataProvider::getSelective($record, ['recordData']);

        $citations = [];

        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo') AS $citation){
            $fullCitation = null;
            $citationMetadata = null;

            if ($citation->fullCitation) {
                $fullCitation = [
                    'value' => (string) $citation->fullCitation,
                    'style' => $citation->fullCitation['style']
                ];
            }

            if ($citation->citationMetadata) {
                $citationMetadata = [
                    'bibtex' => static::toBibTex($citation->citationMetadata, $record),
                    'coins' => static::toCoinsSpan($citation->citationMetadata)
                ];
            }


            $citations[] = [
                'full' => $fullCitation,
                'metadata' => $citationMetadata
            ];
        }

        $bibtex = collect($citations)->filter(function($item) {
            if ($item['metadata'] && $item['metadata']['bibtex']) {
                return $item;
            }
            return false;
        })->first();

        $full = collect($citations)->filter(function($item) {
            if ($item['full']) {
                return $item;
            }
            return false;
        })->first();

        return [
            'full' => $full ? $full['value'] : null,
            'full_style' => $full ? $full['style'] : null,
            'bibtex' => $bibtex ? $bibtex['metadata']['bibtex'] : null,
            'citations' => $citations
        ];
    }

    /**
     * misc {title = "citationMetadata/title>",
     * year = "citationMetadata/date (just the year)",
     * doi = "citationMetadata/identifier @type=doi",
     * author="citationMetadata/contributor",
     * publisher="citationMetadata/publisher" }
     *
     * @param $elem
     * @param RegistryObject $record
     * @return string
     */
    public static function toBibTex($elem, RegistryObject $record)
    {

        $result = "@misc{";
        $result .= "title={".$record->title. "} ";
        if ($date = Carbon::parse((string) $elem->date)) {
            $result .= "year={".$date->year. "} ";
        }
        if ($elem->identifier && $elem->identifier['type'] == 'doi') {
            $result .= "DOI={".(string) $elem->identifier. "} ";
        }
        if ($elem->contributor && $elem->contributor->namePart) {
            $result .= "author={".(string) $elem->contributor->namePart."} ";
        }
        if ($elem->publisher && $elem->publisher) {
            $result .= "publisher={".(string) $elem->publisher."}";
        }
        $result.= "}";

        return trim($result);
    }

    public static function toCoinsSpan($elem)
    {
        return null;
    }
}