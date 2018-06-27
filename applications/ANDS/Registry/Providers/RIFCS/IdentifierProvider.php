<?php


namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use ANDS\Util\XMLUtil;


/**
 * Class IdentifierProvider
 *
 * @package ANDS\Registry\Providers
 */
class IdentifierProvider implements RIFCSProvider
{
    /**
     * Add all Identifiers from rifcs
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function process(RegistryObject $record)
    {
        static::deleteAllIdentifiers($record);
        $identifiers = static::processIdentifiers($record);
        return $identifiers;
    }

    /**
     * Delete Identifiers
     * Clean up before a processing
     *
     * @param RegistryObject $record
     */
    public static function deleteAllIdentifiers(RegistryObject $record)
    {
        Identifier::where('registry_object_id',
            $record->registry_object_id)->delete();
    }


    /**
     * Create Identifiers from current RIFCS
     * TODO: Refactor to use self::get()
     * @param RegistryObject $record
     * @return array
     */
    public static function processIdentifiers(RegistryObject $record)
    {
        $identifiers = [];
        $xml = $record->getCurrentData()->data;
        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $identifierValue = trim((string)$identifier);
            if ($identifierValue == "") {
                continue;
            }
            $identifiers[] = $identifierValue;
            Identifier::create(
                [
                    'registry_object_id' => $record->registry_object_id,
                    'identifier' => $identifierValue,
                    'identifier_type' => trim((string)$identifier['type'])
                ]
            );

        }
        return $identifiers;
    }

    /**
     * Get all identifiers from RIFCS
     * registryObject/:class/identifier
     *
     * @param RegistryObject $record
     * @param null $xml
     * @return array
     */
    public static function get(RegistryObject $record, $xml = null)
    {
        if (!$xml) {
            $xml = $record->getCurrentData()->data;
        }

        $identifiers = [];

        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $identifierValue = trim((string)$identifier);
            if ($identifierValue == "") {
                continue;
            }
            $identifiers[] = [
                'value' => $identifierValue,
                'type' => trim((string)$identifier['type'])
            ];
        }
        return $identifiers;
    }

    /**
     * Get all identifiers from RIFCS
     * registryObject/:class/citationInfo/citationMetadata/identifier
     *
     * @param RegistryObject $record
     * @param null $xml
     * @return array
     */
    public static function getCitationMetadataIdentifiers(
        RegistryObject $record,
        $xml = null
    ) {
        if (!$xml) {
            $xml = $record->getCurrentData()->data;
        }

        $identifiers = [];

        $xpath = "ro:registryObject/ro:{$record->class}/ro:citationInfo/ro:citationMetadata/ro:identifier";

        foreach (XMLUtil::getElementsByXPath($xml, $xpath) AS $identifier) {
            $identifierValue = trim((string)$identifier);
            if ($identifierValue == "") {
                continue;
            }
            $identifiers[] = [
                'value' => $identifierValue,
                'type' => trim((string)$identifier['type'])
            ];
        }

        return $identifiers;
    }

    /**
     * Returns the Human Digestable format for an identifier with a type
     * TODO refactor to make it more readable
     *
     * @param $identifier
     * @param $type
     * @return bool
     * @throws \Exception
     */
    public static function format($identifier, $type)
    {
        switch($type)
        {
            case 'doi':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                elseif(!strpos($identifier,"doi.org/")) $identifier_href ="http://dx.doi.org/".$identifier;
                else $identifier_href = "http://dx.doi.org/".substr($identifier,strpos($identifier,"doi.org/")+8);
                $identifiers['href'] = $identifier_href;
                $identifiers['display_text'] = strtoupper($type);
                $identifiers['hover_text'] = 'Resolve this DOI';
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/doi_icon.png alt="DOI Link"/>';
                return  $identifiers;
                break;
            case 'ark':
                $identifiers['href'] = '';
                $identifiers['display_icon'] = '';
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier && str_replace('/ark:/','',$identifier)!=$identifier){
                    $identifiers['href'] = $identifier;
                    $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/external_link.png alt="External Link"/>';
                }
                elseif(strpos($identifier,'/ark:/')>1){
                    $identifiers['href'] = 'http://'.$identifier;
                    $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/external_link.png alt="External Link"/>';
                }
                $identifiers['display_text'] = 'ARK';
                $identifiers['hover_text'] = 'Resolve this ARK identifier';
                return $identifiers;
                break;
            case 'orcid':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                elseif(!strpos($identifier,"orcid.org/")) $identifier_href ="http://orcid.org/".$identifier;
                else $identifier_href = "http://orcid.org/".substr($identifier,strpos($identifier,"orcid.org/")+10);
                $identifiers['href'] = $identifier_href;
                $identifiers['display_text'] = 'ORCID';
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/orcid_icon.png alt="ORCID Link"/>';
                $identifiers['hover_text'] = 'Resolve this ORCID';
                return  $identifiers;
                break;
            case 'AU-ANL:PEAU':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                elseif(!strpos($identifier,"nla.gov.au/")) $identifier_href ="http://nla.gov.au/".$identifier;
                else $identifier_href = "http://nla.gov.au/".substr($identifier,strpos($identifier,"nla.gov.au/")+11);
                $identifiers['href'] = $identifier_href;
                $identifiers['display_text'] = 'NLA';
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/nla_icon.png alt="NLA Link"/>';
                $identifiers['hover_text'] = 'View the record for this party in Trove';
                return  $identifiers;
                break;
            case 'handle':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                elseif(strpos($identifier,"dl:")>0) $identifier_href ="http://hdl.handle.net/".substr($identifier,strpos($identifier,"hdl:")+4);
                elseif(strpos($identifier,"dl.handle.net/")>0) $identifier_href ="http://hdl.handle.net/".substr($identifier,strpos($identifier,"hdl.handle.net/")+15);
                else $identifier_href = "http://hdl.handle.net/".$identifier;
                $identifiers['href'] = $identifier_href;
                $identifiers['display_text'] = 'Handle';
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/handle_icon.png alt="Handle Link"/>';
                $identifiers['hover_text'] = 'Resolve this handle';
                return  $identifiers;
                break;
            case 'raid':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                elseif(strpos($identifier,"dl:")>0) $identifier_href ="http://hdl.handle.net/".substr($identifier,strpos($identifier,"hdl:")+4);
                elseif(strpos($identifier,"dl.handle.net/")>0) $identifier_href ="http://hdl.handle.net/".substr($identifier,strpos($identifier,"hdl.handle.net/")+15);
                else $identifier_href = "http://hdl.handle.net/".$identifier;
                $identifiers['href'] = $identifier_href;
                $identifiers['display_text'] = 'RAID';
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/handle_icon.png alt="Handle Link"/>';
                $identifiers['hover_text'] = 'Resolve this handle';
                return  $identifiers;
                break;
            case 'purl':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                elseif(strpos($identifier,"url.org/")<1) $identifier_href ="http://purl.org/".$identifier;
                else $identifier_href = "http://purl.org/".substr($identifier,strpos($identifier,"purl.org/")+9);
                $identifiers['href'] = $identifier_href;
                $identifiers['display_text'] = 'PURL';
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/external_link.png alt="External Link"/>';
                $identifiers['hover_text'] = 'Resolve this PURL';
                return  $identifiers;
                break;
            case 'isni':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                elseif(strpos($identifier,"isni.org/")<1) $identifier_href ="http://isni.org/".$identifier;
                else $identifier_href = "http://isni.org/".substr($identifier,strpos($identifier,"isni.org/")+9);
                $identifiers['href'] = $identifier_href;
                $identifiers['display_text'] = 'ISNI';
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/external_link.png alt="External Link"/>';
                $identifiers['hover_text'] = 'Resolve this ISNI';
                return  $identifiers;
                break;
            case 'igsn':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                elseif(strpos($identifier,"igsn.org/")<1) $identifier_href ="http://igsn.org/".$identifier;
                else $identifier_href = "http://igsn.org/".substr($identifier,strpos($identifier,"igsn.org/")+9);
                $identifiers['href'] = $identifier_href;
                $identifiers['display_text'] = 'IGSN';
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/external_link.png alt="External Link"/>';
                $identifiers['hover_text'] = 'Resolve this IGSN';
                return  $identifiers;
                break;
            case 'grid':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                if(isset($identifier_href)) {
                    $identifiers['href'] = $identifier_href;
                    $identifiers['display_icon'] = '<img class="identifier_logo" src= ' . baseUrl() . 'assets/core/images/icons/external_link.png alt="External Link"/>';

                }
                $identifiers['display_text'] = 'GRID';
                return $identifiers;
                break;
            case 'scopusID':
                if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier;
                if(isset($identifier_href)) {
                    $identifiers['href'] = $identifier_href;
                    $identifiers['display_icon'] = '<img class="identifier_logo" src= ' . baseUrl() . 'assets/core/images/icons/external_link.png alt="External Link"/>';

                }
                $identifiers['display_text'] = 'Scopus';
                return $identifiers;
                break;
            case 'uri':
                $identifiers['href'] = $identifier;
                $identifiers['display_text'] = strtoupper($type);
                $identifiers['hover_text'] = 'Resolve this URI';
                $identifiers['display_icon'] = '<img class="identifier_logo" src= '.baseUrl().'assets/core/images/icons/external_link.png alt="External Link"/>';
                return $identifiers;
                break;
            case 'urn':
                break;
            case 'local':
                $identifiers['display_text'] = 'Local';
                return $identifiers;
                break;
            case 'isil':
                $identifiers['display_text'] = 'ISIL';
                return $identifiers;
                break;
            case 'abn':
                $identifiers['display_text'] = 'ABN';
                return $identifiers;
                break;
            case 'arc':
                $identifiers['display_text'] = 'ARC';
                return $identifiers;
                break;
            default:
                return false;
                break;
        }


        throw new \Exception("$type not supported for formatting");
    }
}