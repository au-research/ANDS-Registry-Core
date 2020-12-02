<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;


/**
 * Class AltmetricsProvider
 * @package ANDS\Registry\Providers
 */
class AltmetricsProvider implements RIFCSProvider
{

    /**
     * Process the object and return processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        return;
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        return self::getAltmetrics($record);
    }

    public static function getAltmetrics( RegistryObject $record ) {
        $altmetrics = [];

        /* dc.title */
        $altmetrics[] = [
            'type' => "dc.title",
            'value' => (string) self::getTitle($record )
        ];


        /* dc.identifier */
        $identifiers = self::getIdentifier($record);
        foreach($identifiers as $identifier) {
            $altmetrics[] = [
                'type' => "dc.identifier",
                'value' => (string)$identifier
            ];
        }

        /* citation_publisher */
        $altmetrics[] = [
            'type' => "citation_provider",
            'value' => (string) self::getPublisher($record )
        ];

        /* dc.creator */
        $creators = self::getCreators($record );
        foreach($creators as $creator) {
            $altmetrics[] = [
                'type' => "dc.creator",
                'value' => (string)$creator["name"]
            ];
        }

        /* citation_online_date */
        if($publicationDate = self::getPublicationDate($record)) {
            $altmetrics[] = [
                'type' => "citation_online_date",
                'value' => (string)$publicationDate
            ];
        }
        return $altmetrics;
    }

    /**
     * Get publisher for the record
     * Spec for Altmetrics
     * @param RegistryObject $record
     * @param null $simpleXML
     * @return string
     * @throws \Exception
     */
    public static function getPublisher(RegistryObject $record, $simpleXML = null)
    {
        /**
         * registryObject:collection:citationInfo:citationMetadata:publisher
            OR registryObject@Group
         */
        $simpleXML = $simpleXML ? $simpleXML : MetadataProvider::getSimpleXML($record);

        // registryObject:collection:citationInfo:citationMetadata:publisher
        $publisher = $simpleXML->xpath('//ro:citationInfo/ro:citationMetadata/ro:publisher');
        if (count($publisher) > 0 && $elem = array_pop($publisher)) {
            return (string) $elem;
        }

        return $record->group;
    }

    /**
     * Get title for the record
     * Spec for Altmetrics
     * @param RegistryObject $record
     * @param null $simpleXML
     * @return string
     * @throws \Exception
     */
    public static function getTitle(RegistryObject $record, $simpleXML = null)
    {
        /**
         * registryObject:collection:citationInfo:citationMetadata:title
        OR RDA display title
         */
        $simpleXML = $simpleXML ? $simpleXML : MetadataProvider::getSimpleXML($record);

        //registryObject:collection:citationInfo:citationMetadata:title
        $title = $simpleXML->xpath('//ro:citationInfo/ro:citationMetadata/ro:title');
        if (count($title) > 0 && $elem = array_pop($title)) {
            return (string) $elem;
        }
        return $record->title;
    }

    /**
     * Get identifier for the record
     * Spec for Altmetrics
     * @param RegistryObject $record
     * @param null $simpleXML
     * @return string
     * @throws \Exception
     */
    public function getIdentifier(RegistryObject $record, $simpleXML = null)
    {
        /**
         * registryObject:collection:citationInfo:citationMetadata:identifier
         * OR registryObject:collection:identifier
         * OR
         */
        $identifiers = [];

        $simpleXML = $simpleXML ? $simpleXML : MetadataProvider::getSimpleXML($record);

        //registryObject:collection:citationInfo:citationMetadata:identifier
        $ids = $simpleXML->xpath('//ro:citationInfo/ro:citationMetadata/ro:identifier');
        foreach($ids as $identifier){
            $identifiers[] = (string) $identifier;
        }
        if (count($identifiers) > 0) {
            return $identifiers;
        }

        //registryObject:collection:identifier
        $ids = $simpleXML->xpath('//ro:collection/ro:identifier');
        foreach($ids as $identifier){
            $identifiers[] = (string) $identifier;
        }
        if (count($identifiers) > 0 ) {
            return $identifiers;
        }
        $identifiers[] = $record->key;
        return $identifiers;
    }


    /**
     * @param RegistryObject $record
     * @param null $simpleXML
     * @return array
     * @throws \Exception
     */
    public function getCreators(RegistryObject $record, $simpleXML = null)
    {
        $simpleXML = $simpleXML ? $simpleXML : MetadataProvider::getSimpleXML($record);

        /**
         * registryObject:collection:citationInfo:citationMetadata:contributor
        OR relatedObject:Party:name:relationType=IsPrincipalInvestigatorOf
        OR relatedObject:Party:name:relationType=author
        OR relatedObject:Party:name:relationType=coInvestigator
        OR relatedObject:Party:name:relationType=isOwnedBy
        OR relatedObject:Party:name:relationType=hasCollector
        OR registryObject@Group
         */
        $creators = [];

        $contributors = $simpleXML->xpath('//ro:citationInfo/ro:citationMetadata/ro:contributor');

        foreach($contributors as $contributor){
            $nameParts = Array();
            foreach ($contributor->namePart as $namePart) {
                $nameParts[] = array(
                    'namePart_type' => (string)$namePart['type'],
                    'name' => (string)$namePart
                );
            }
            $name = formatName($nameParts);
            if(trim($name)!="")
            $creators[] = [ 'name' => $name ];
        }

        if (count($creators)) {
            return $creators;
        }

        $validRelationTypes[] = ['IsPrincipalInvestigatorOf', 'hasPrincipalInvestigator'];
        $validRelationTypes[] = ['author'];
        $validRelationTypes[] = ['coInvestigator'];
        $validRelationTypes[] = ['isOwnedBy','IsOwnerOf'];
        $validRelationTypes[] = ['hasCollector','IsCollectorOf'];
        foreach ($validRelationTypes as $relationTypes) {
            $creators = array_merge($creators, RelationshipProvider::getRelationByType($record, $relationTypes));
            if (count($creators)) {
                return $creators;
            }
        }

        if (!count($creators)) {
            $creators[] = [
                'name' => (string) $record->group,
            ];
        }

        return $creators;
    }



    public static function getPublicationDate(
        RegistryObject $record,
        $data = null,
        $format = 'Y-m-d'
    ) {
        if (!$data) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
        }

        $publicationDate = null;

        $citationMedataDates = XMLUtil::getElementsByXPath(
            $data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:citationInfo/ro:citationMetadata/ro:date'
        );

        // registryObject/collection/citationInfo/citationMetadata/date[@type=’publication date’]
        foreach ($citationMedataDates AS $date) {
            if ((string) $date['type'] == 'publicationDate') {
                $publicationDate = DatesProvider::formatDate((string) $date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        // registryObject/collection/citationInfo/citationMetadata/date[@type=’issued date’]
        foreach ($citationMedataDates AS $date) {
            if ((string) $date['type'] == 'issued') {
                $publicationDate = DatesProvider::formatDate((string) $date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        // registryObject/collection/citationInfo/citationMetadata/date[@type=’created’]
        foreach ($citationMedataDates AS $date) {
            if ((string) $date['type'] == 'created') {
                $publicationDate = DatesProvider::formatDate((string) $date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        $roDates = XMLUtil::getElementsByXPath(
            $data['recordData'],
            'ro:registryObject/ro:' . $record->class . '/ro:dates'
        );

        // registryObject/collection/dates[@type=’issued’]
        foreach ($roDates AS $date) {
            if ((string) $date['type'] == 'dc.issued') {
                $publicationDate = DatesProvider::formatDate((string) $date->date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        // registryObject/collection/dates[@type=’available’]
        foreach ($roDates AS $date) {
            if ((string) $date['type'] == 'dc.available') {
                $publicationDate = DatesProvider::formatDate((string) $date->date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        // registryObject/collection/dates[@type=’created’]
        foreach ($roDates AS $date) {
            if ((string) $date['type'] == 'dc.created') {
                $publicationDate = DatesProvider::formatDate((string) $date->date, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        /**
         * registryObject/collection@dateModified
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class) AS $object) {

            if ($dateModified = (string) $object['dateModified']) {
                $publicationDate = DatesProvider::formatDate($dateModified, $format);
            }
        }
        if ($publicationDate) return $publicationDate;


        /**
         * registryObject/Collection@dateAccessioned
         */
        foreach (XMLUtil::getElementsByXPath($data['recordData'],
            'ro:registryObject/ro:' . $record->class) AS $object) {

            if ($dateAccessioned = (string) $object['dateAccessioned']) {
                $publicationDate = DatesProvider::formatDate($dateAccessioned, $format);
            }
        }
        if ($publicationDate) return $publicationDate;

        return DatesProvider::getCreatedDate($record, $format);
    }

    //function to concatenate name values based on the name part type
    function formatName($a)
    {
        $order = array('family','given','initial','title','superior');
        $displayName = '';
        foreach($order as $o){
            $givenFound = false;
            foreach($a as $namePart)
            {
              $displayName = implode(",",$namePart);
                  if($namePart['namePart_type']==$o) {
                     if($namePart['namePart_type']=='given') $givenFound = true;
                     if($namePart['namePart_type']=='initial' && $givenFound) $namePart['name']='';
                     else $displayName .=  $namePart['value'].", ";
                 }
            }

        }
       foreach($a as $namePart)
        {
            if(!$namePart['type']) {
                $displayName .=  $namePart['name'].", ";
            }
        }
        return trim($displayName,", ")." ";
    }

}