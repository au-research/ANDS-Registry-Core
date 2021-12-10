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
            if (trim((string)$identifier) == "") {
                continue;
            }
            $normalisedIdentifier = IdentifierProvider::getNormalisedIdentifier(trim((string)$identifier), trim((string)$identifier['type']));

            $identifiers[] = $normalisedIdentifier["value"];
            Identifier::create(
                [
                    'registry_object_id' => $record->registry_object_id,
                    'identifier' => $normalisedIdentifier["value"],
                    'identifier_type' => $normalisedIdentifier["type"]
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
     * @param RegistryObject $record
     * @param null $xml
     * @return array
     */
    public static function getRelatedInfoIdentifiers(RegistryObject $record, $xml = null)
    {
        if (!$xml) {
            $xml = $record->getCurrentData()->data;
        }

        $identifiers = [];

        $xpath = "ro:registryObject/ro:{$record->class}/ro:relatedInfo/ro:identifier";

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
                //if(str_replace('http://','',str_replace('https://','',$identifier))!=$identifier) $identifier_href =$identifier."mystuff";
                if(!strpos($identifier,"doi.org/")) $identifier_href ="https://doi.org/".$identifier;
                else $identifier_href = "https://doi.org/".substr($identifier,strpos($identifier,"doi.org/")+8);
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
            case 'url':
            case 'uri':
                // url and uri should have been stripped off ther http protocol, but some legacy Identifier may still have them
                $identifiers['href'] = "https://" . preg_replace("(^https?://)", "", $identifier );
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

    /**
     * returns a standard representation for any given Identifier
     * this is needed to match Identifiers that are identical but using a different form
     * eg: all these identifiers are identical for type="doi"
     * DOI:10.234/455
     * http://doi.org/10.234/455
     * https://doi.org/10.234/455
     * 10.234/455
     * @param $identifierValue
     * @param $type
     */
    public static function getNormalisedIdentifier($identifierValue, $type){

        // first overwrite type if it is not specific enough
        $identifier["type"] = IdentifierProvider::getNormalisedIdentifierType($identifierValue, $type);
        $identifier["value"] = $identifierValue;

        switch ($identifier["type"])
        {
            case "doi":
                // if it's a valid DOI eg there is a string that starts with 10.
                // upper case DOI values they are case insensitive
                $identifierValue = strtoupper(trim($identifierValue));
                if(str_contains($identifierValue, "10.")){
                    $identifier["value"] = strtoupper(substr($identifierValue, strpos($identifierValue, "10.")));
                }
                return $identifier;
                break;
            case "orcid":
                // ORCID is 19 character long with 4 sets of 4 digit numbers
                if(substr_count($identifierValue, "-") >= 3){
                    $identifier["value"] = strtoupper(substr($identifierValue, strpos($identifierValue, "-") - 4, 19));
                }
                return $identifier;
                break;
            case "handle":
                $identifierValue = strtolower(trim($identifierValue));
                if(str_contains($identifierValue, "hdl:")){
                    $identifier["value"] = substr($identifierValue, strpos($identifierValue, "hdl:") + 4);
                }
                else if(str_contains($identifierValue, "http")){
                    $parsedUrl = parse_url($identifierValue);
                    $identifier["value"] = substr($parsedUrl["path"],1);
                }
                else if(str_contains($identifierValue, "handle.")){
                    $parsedUrl = parse_url("http://".$identifierValue);
                    $identifier["value"] = substr($parsedUrl["path"],1);
                }
                return $identifier;
                break;
            case "purl":
                if(str_contains($identifierValue, "purl.org")){
                    $identifier["value"] = "https://" . substr($identifierValue, strpos($identifierValue, "purl.org"));
                }
                return $identifier;
                break;
            case "AU-ANL:PEAU":
                if(str_contains($identifierValue, "nla.party-")){
                    $identifier["value"] = substr($identifierValue, strpos($identifierValue, "nla.party-"));
                }
                if(str_contains($identifierValue, "party-")){
                    $identifier["value"] = "nla.party-" . substr($identifierValue, strpos($identifierValue, "party-") + 6);
                }
                elseif(strpos($identifierValue, 'http://') === 0 || strpos($identifierValue, 'https://') === 0){
                    $identifier["value"] = $identifierValue;
                }
                else{
                    $identifier["value"] = "nla.party-".$identifierValue;
                }
                return $identifier;
                break;
            case "igsn":
                // upper case IGSN values they are case insensitive
                $identifierValue = strtoupper(trim($identifierValue));
                $identifier["value"] = $identifierValue;
                if(str_contains($identifierValue, "10273/")){
                    $identifier["value"] = substr($identifierValue, strpos($identifierValue, "10273/") + 6);
                }
                else if(str_contains($identifierValue, "IGSN.ORG/")){
                    $identifier["value"] = substr($identifierValue, strpos($identifierValue, "IGSN.ORG/") + 9);
                }
                return $identifier;
                break;
            case 'uri':
            case 'url':
                // RDA-141  remove http or https protocol for all other identifiers
                // changed at 08/12/2021
                // RDA-584 remove them only for uri and url instead
                return preg_replace("(^https?://)", "", $identifier );
                break;
            default:
                // leave all other Identifier intact
                return $identifier;
        }

    }

    /**
     * trying to best guess the more specific IdentifierType based on the Identifier value
     * or a regular missmatch from
     * eg: uri with value http://doi.org/10.5412 should be changed to doi
     * @param $identifierValue
     * @param $type
     * @return string
     */
    public static function getNormalisedIdentifierType($identifierValue, $type){

        $identifierValue = strtoupper(trim($identifierValue));

        // first overwrite type is it's not correct
        if(strtolower($type) == "nla.party"){
            return "AU-ANL:PEAU";
        }
        if(str_contains($identifierValue, "HDL.HANDLE.NET/10273/")){
            return "igsn";
        }
        if(strpos($identifierValue, "10.") > 0  && strpos($identifierValue, "DOI") > 0){
            return "doi";
        }
        if(strpos($identifierValue, "ORCID.ORG") > 0  && substr_count($identifierValue, "-") >= 3){
            return "orcid";
        }
        if(strpos($identifierValue, "HANDLE.") > 0  || str_contains($identifierValue, "HDL:")){
            if(substr_count($identifierValue, "HTTP:") > 1){
                // unable to confirm it's a handle
                return $type;
            }
            return "handle";
        }
        if(strpos($identifierValue, "PURL.ORG") > 0){
            return "purl";
        }
        if(str_contains($identifierValue, "NLA.PARTY-")){
            return "AU-ANL:PEAU";
        }
        return $type;
    }
}