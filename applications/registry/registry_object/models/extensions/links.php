<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 17/11/14
 * Time: 9:45 AM
 */


class links_Extension extends ExtensionBase
{

    function __construct($ro_pointer)
    {
        parent::__construct($ro_pointer);
    }

    function processLinks()
    {
        $sXml = $this->ro->getSimpleXML();
        $sXml->registerXPathNamespace("ro", RIFCS_NAMESPACE);
        $eaLinks = $this->processElectronicAddresses($sXml);
        $cmUrls = $this->processCitationUrl($sXml);
        $descLinks = $this->processDescriptions($sXml);
        $identifierLinks = $this->processIdentifierLinks($sXml);
        $relationLinks = $this->processRelationUrl($sXml);
        $allLinks = array_merge($eaLinks, $cmUrls, $descLinks, $identifierLinks,
                                $relationLinks);
        //find existing links for this ro
        $currentLinks = $this->getExistingLinks();
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
        $this->deleteLinks($removedLinks);
        $this->storeLinks($newLinks);
    }

    function processElectronicAddresses($sXml){
        $eaLinks = array();
        $ro_id = $this->ro->id;
        $ds_id = $this->ro->data_source_id;
        $electronic_address = $sXml->xpath('//ro:electronic');
        foreach ($electronic_address AS $address)
        {
            $type = 'electronic_'.(string)$address["type"];
            $value = (string)$address->value[0];
            array_push($eaLinks, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$value,'status'=>'NEW')));
        }
        return $eaLinks;
    }

    function processCitationUrl($sXml)
    {
        $cUrls = array();
        $ro_id = $this->ro->id;
        $ds_id = $this->ro->data_source_id;
        // The url is optional, so only select elements that have one.
        $citation_metadata = $sXml->xpath('//ro:citationMetadata[ro:url]');
        foreach ($citation_metadata AS $cm)
        {
            $type = 'citation_metadata_url';
            $value = (string)$cm->url[0];
            array_push($cUrls, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$value,'status'=>'NEW')));
        }
        return $cUrls;
    }

    function processDescriptions($sXml)
    {
        $descLinks = array();
        $ro_id = $this->ro->id;
        $ds_id = $this->ro->data_source_id;
        $descriptions = $sXml->xpath('//ro:description');
        foreach ($descriptions AS $desc)
        {
            $regex = '/https?\:\/\/[^\"\'\s<]+/i';
            preg_match_all($regex, html_entity_decode($desc), $matches);
            $type = 'description_link';
            foreach($matches[0] as $url){
                array_push($descLinks, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$url,'status'=>'NEW')));
            }
        }
        return $descLinks;
    }

    function processIdentifierLinks($sXml) {

        $identifiersLinks = array();
        $ro_id = $this->ro->id;
        $ds_id = $this->ro->data_source_id;
        foreach($sXml->xpath('//ro:'.$this->ro->class.'/ro:identifier') AS $identifier) {
            if((string)$identifier != '') {
                $vType = strtolower((string) $identifier['type']);
                $type = 'identifier_'.$vType.'_link';
                $link = $this->getResolvedLinkForIdentifier($vType, (string) $identifier);
                if($link != '')
                {
                    array_push($identifiersLinks, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$link,'status'=>'NEW')));
                }
            }
        }
        foreach($sXml->xpath('//ro:'.$this->ro->class.'/ro:relatedInfo/ro:identifier') AS $identifier) {
            if((string)$identifier != '') {
                $vType = strtolower((string) $identifier['type']);
                $type = 'identifier_'.$vType.'_link';
                $link = $this->getResolvedLinkForIdentifier($vType, (string) $identifier);
                if($link != '')
                {
                    array_push($identifiersLinks, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$link,'status'=>'NEW')));
                }
            }
        }
        foreach($sXml->xpath('//ro:'.$this->ro->class.'/ro:citationInfo/ro:citationMetadata/ro:identifier') AS $identifier) {
            if((string)$identifier != '') {
                $vType = strtolower((string) $identifier['type']);
                $type = 'citation_metadata_identifier_'.$vType.'_link';
                $link = $this->getResolvedLinkForIdentifier($vType, (string) $identifier);
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
    function processRelationUrl($sXml)
    {
        $rUrls = array();
        $ro_id = $this->ro->id;
        $ds_id = $this->ro->data_source_id;
        // The url is optional, so only select elements that have one.
        $relation_metadata =
          $sXml->xpath('//ro:relation[ro:url]');
        foreach ($relation_metadata AS $rm)
        {
            $rmParent = $rm->xpath('..');
            $rType = strtolower((string) $rm['type']);
            $type = $rmParent[0]->getName() . '_relation_' . $rType. '_url';
            $value = (string)$rm->url[0];
            array_push($rUrls, json_encode(array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$value,'status'=>'NEW')));
        }
        return $rUrls;
    }

    function getResolvedLinkForIdentifier($type, $value)
    {
        switch ($type){
            case 'handle':
                if (strpos($value,'http://hdl.handle.net/') === false){
                    $value = 'http://hdl.handle.net/'.$value;
                }
                return $value;
                break;
            case 'purl':
                if (strpos($value,'http://purl.org/') === false){
                    $value = 'http://purl.org/'.$value;
                }
                return $value;
                break;
            case 'doi':
                if (strpos($value,'http://dx.doi.org/') === false){
                    $value = 'http://dx.doi.org/'.$value;
                }
                return $value;
                break;
            case 'uri':
                if (strpos($value,'http://') === false && strpos($value,'https://') === false){
                    $value = 'http://'.$value;
                }
                return $value;
                break;
            case 'orcid':
                if (strpos($value,'http://orcid.org/') === false){
                    $value = 'http://orcid.org/'.$value;
                }
                return $value;
                break;
            case 'au-anl:peau':
                if (strpos($value,'http://nla.gov.au/') === false){
                    $value = 'http://nla.gov.au/'.$value;
                }
                return $value;
                break;
            default:
                return "";
        }
    }

    function storeLinks($jsonLinksArray)
    {
        foreach($jsonLinksArray as $link){
            $link = json_decode($link, true);
            $this->db->insert('registry_object_links',$link);
        }
    }

    function deleteLinks($jsonLinksArray)
    {
        foreach($jsonLinksArray as $link){
            $link = json_decode($link, true);
            $this->db->where(array("registry_object_id" => $link["registry_object_id"],
                'data_source_id'=>$link['data_source_id'],
                'link_type'=>$link['link_type'],
                'link'=>$link['link']));
            $this->db->delete("registry_object_links");
        }
    }

    function getExistingLinks()
    {
        $eLinksArray = array();
        $this->db->select("registry_object_id, data_source_id, link_type, link");
        $this->db->where('registry_object_id',(string)$this->ro->id);
        $result = $this->db->get('registry_object_links');
        foreach ($result->result_array() AS $row)
        {
            $row['status'] = 'NEW';
            $eLinksArray[] = json_encode($row);
        }
        return $eLinksArray;
    }

}

