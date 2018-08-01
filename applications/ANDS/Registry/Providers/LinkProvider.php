<?php


namespace ANDS\Registry\Providers;



use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use ANDS\RegistryObject\Links;

class LinkProvider implements RegistryContentProvider
{

    public static function process(RegistryObject $record)
    {
         $recordData = $record->getCurrentData();
         return static::processLinks($record, $recordData->data);
    }


    public static function processLinks($record, $xml = false)
    {
        if (!$xml) {
            $xml = $record->getCurrentData();
        }

        $eaLinks = static::processElectronicAddresses($record, $xml);
        $cmUrls = static::processCitationUrl($record, $xml);
        $descLinks = static::processDescriptions($record, $xml);
        $identifierLinks = static::processIdentifierLinks($record, $xml);
        $relationLinks = static::processRelationUrl($record, $xml);

        $allLinks = array_merge($eaLinks, $cmUrls, $descLinks, $identifierLinks,
            $relationLinks);

        //find existing links for this ro
        $currentLinks = static::getExistingLinks($record);

        // print "<br />currentLinks = <br />";
        // var_dump($currentLinks);
        // print "<br /><br />";
        // get existing - all = links to delete
        $removedLinks = array_diff($currentLinks, $allLinks);


        // print "<br />allLinks = <br />";
        // var_dump($allLinks);
        // print "<br />removedLinks = <br />";
        // var_dump($removedLinks);
        // new links - current to insert
        $newLinks = array_diff($allLinks, $currentLinks);
        // the rest remains the same
        static::deleteLinks($removedLinks);
        static::storeLinks($newLinks);
        return count($allLinks);
    }

    public static function processElectronicAddresses($record, $xml){
        $eaLinks = array();
        $ro_id = (string)$record->id;
        $ds_id = (string)$record->data_source_id;
        $electronic_address = XMLUtil::getElementsByXPath($xml, '//ro:electronic[@type = "url" or @type = "uri"]');
        foreach ($electronic_address AS $address)
        {
            $type = 'electronic_'.(string)$address["type"];
            $value = trim((string)$address->value[0]);
            if ($value != '') {
                array_push($eaLinks, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$value,'status'=>'NEW')));
            }
        }
        return $eaLinks;
    }

    public static function processCitationUrl($record, $xml)
    {
        $cUrls = array();
        $ro_id = (string)$record->id;
        $ds_id = (string)$record->data_source_id;
        // The url is optional, so only select elements that have one.
        $citation_metadata = XMLUtil::getElementsByXPath($xml, '//ro:citationMetadata[ro:url]');
        foreach ($citation_metadata AS $cm)
        {
            $type = 'citation_metadata_url';
            $value = trim((string)$cm->url[0]);
            if ($value != '') {
                array_push($cUrls, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$value,'status'=>'NEW')));
            }
        }
        return $cUrls;
    }

    public static function processDescriptions($record, $xml)
    {
        $descLinks = array();
        $ro_id = (string)$record->id;
        $ds_id = (string)$record->data_source_id;
        $descriptions = XMLUtil::getElementsByXPath($xml, '//ro:description');
        foreach ($descriptions AS $desc)
        {
            $regex = '/https?\:\/\/[^\"\'\s<]+/i';
            preg_match_all($regex, html_entity_decode($desc), $matches);
            $type = 'description_link';
            foreach($matches[0] as $url){
                $url = trim($url,"):.,");
                if ($url != '') {
                    array_push($descLinks, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$url,'status'=>'NEW')));
                }
            }
        }
        return $descLinks;
    }

    public static function processIdentifierLinks($record, $xml) {

        $identifiersLinks = array();
        $ro_id = (string)$record->id;
        $ds_id = (string)$record->data_source_id;
        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $vType = strtolower((string) $identifier['type']);
            $type = 'identifier_'.$vType.'_link';
            $link = trim(static::getResolvedLinkForIdentifier($vType, (string) $identifier));
            if ($link == "") {
                continue;
            }
            array_push($identifiersLinks, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$link,'status'=>'NEW')));
        }

        foreach(XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class.'/ro:relatedInfo/ro:identifier') AS $identifier) {
            if((string)$identifier != '') {
                $vType = strtolower((string) $identifier['type']);
                $type = 'identifier_'.$vType.'_link';
                $link = static::getResolvedLinkForIdentifier($vType, (string) $identifier);
                if($link != '')
                {
                    array_push($identifiersLinks, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$link,'status'=>'NEW')));
                }
            }
        }
        foreach( XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class.'/ro:citationInfo/ro:citationMetadata/ro:identifier') AS $identifier) {
            if((string)$identifier != '') {
                $vType = strtolower((string) $identifier['type']);
                $type = 'citation_metadata_identifier_'.$vType.'_link';
                $link = self::getResolvedLinkForIdentifier($vType, (string) $identifier);
                if($link != '')
                {
                    array_push($identifiersLinks, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$link,'status'=>'NEW')));
                }
            }
        }
        return $identifiersLinks;
    }

    // relatedObject/relation/url and relatedInfo/relation/url
    // NB "type" attribute is required in both cases, and we use it in
    // generating the link_type.
    public static function processRelationUrl($record, $xml)
    {
        $rUrls = array();
        $ro_id = (string)$record->id;
        $ds_id = (string)$record->data_source_id;
        // The url is optional, so only select elements that have one.
        foreach (XMLUtil::getElementsByXPath($xml, '//ro:relation[ro:url]') AS $rm)
        {
            $rmParent = $rm->xpath('..');
            $rType = strtolower((string) $rm['type']);
            $type = $rmParent[0]->getName() . '_relation_' . $rType. '_url';
            $value = trim ((string)$rm->url[0]);
            if ($value != '') {
                array_push($rUrls, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$value,'status'=>'NEW')));
            }
        }
        return $rUrls;
    }

    public static function getResolvedLinkForIdentifier($type, $identifier)
    {
        $identifier = trim($identifier);
        $typeArray = ['handle', 'purl', 'doi', 'uri', 'url', 'ark', 'orcid', 'au-anl:peau' , 'raid', 'grid','scopusID','igsn','isni'];

        if ((strpos($identifier,'http://') === 0 || strpos($identifier,'https://') === 0)
            && in_array($type, $typeArray)){
            return $identifier;
        }

        switch ($type){
            case 'handle':
                if(strpos($identifier,"hdl:") === 0) {
                    return "http://hdl.handle.net/" . substr($identifier, strpos($identifier, "hdl:") + 4);
                }
                elseif(strpos($identifier, "hdl.handle.net/") === 0)
                    return "http://hdl.handle.net/" . substr($identifier, strpos($identifier, "hdl.handle.net/") + 15);
                else
                    return "http://hdl.handle.net/" . $identifier;
                break;
            case 'raid':
                if(strpos($identifier,"hdl:") === 0) {
                    return "http://hdl.handle.net/" . substr($identifier, strpos($identifier, "hdl:") + 4);
                }
                elseif(strpos($identifier, "hdl.handle.net/") === 0)
                    return "http://hdl.handle.net/" . substr($identifier, strpos($identifier, "hdl.handle.net/") + 15);
                else
                    return "http://hdl.handle.net/" . $identifier;
                break;
            case 'purl':
                if(strpos($identifier,"purl.org/") === false)
                    return "http://purl.org/".$identifier;
                else
                    return "http://purl.org/" . substr($identifier, strpos($identifier, "purl.org/") + 9);
                break;
            case 'igsn':
                if(strpos($identifier,"igsn.org/") === false)
                    return "http://igsn.org/".$identifier;
                else
                    return "http://igsn.org/" . substr($identifier, strpos($identifier, "igsn.org/") + 9);
                break;
            case 'isni':
                if(strpos($identifier,"isni.org/") === false)
                    return "http://www.isni.org/".$identifier;
                else
                    return "http://www.isni.org/" . substr($identifier, strpos($identifier, "isni.org/") + 9);
                break;
            case 'doi':
                if(strpos($identifier,"doi.org/") === false )
                    return "http://dx.doi.org/" . $identifier;
                else
                    return "http://dx.doi.org/" . substr($identifier, strpos($identifier, "doi.org/") + 8 );
                break;
            case 'uri':
                return 'http://'  .$identifier;
                break;
            case 'url':
                return 'http://'  .$identifier;
                break;
            case 'ark':
                if(strpos($identifier,'/ark:/') > 1)
                    return "http://" . $identifier;
                break;
            case 'orcid':
                if(strpos($identifier,"orcid.org/") === false)
                    return "http://orcid.org/" . $identifier;
                else
                    return "http://orcid.org/" . substr($identifier, strpos($identifier, "orcid.org/" ) + 10);
                break;
            case 'au-anl:peau':
                if(strpos($identifier,"nla.gov.au/") === false)
                    return "http://nla.gov.au/" . $identifier;
                else
                    return "http://nla.gov.au/" . substr($identifier, strpos($identifier, "nla.gov.au/") + 11);
                break;

            default:
                return "";
        }
    }

    public static function storeLinks($jsonLinksArray)
    {
        foreach($jsonLinksArray as $link){
            $link = json_decode($link, true);
            if (is_array($link) && count($link)) {
                Links::create($link);
            }
        }
    }

    public static function deleteLinks($jsonLinksArray)
    {
        foreach($jsonLinksArray as $link){
            $link = json_decode($link, true);
            Links::where(array("registry_object_id" => $link["registry_object_id"],
                'data_source_id'=>$link['data_source_id'],
                'link_type'=>$link['link_type'],
                'link'=>$link['link']))->delete();
        }
    }

    public static function getExistingLinks($record)
    {
        $eLinksArray = array();
        $existingLinks = Links::where('registry_object_id',(string)$record->id)
            ->select('registry_object_id','data_source_id','link_type','link')->get();

        foreach ($existingLinks AS $link)
        {
            $link['status'] = 'NEW';
            $eLinksArray[] = json_encode($link);
        }
        return $eLinksArray;
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record)
    {
        // TODO: Implement get() method.
    }
}

