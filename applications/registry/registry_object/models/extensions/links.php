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
        $allLinks = array_merge($eaLinks, $cmUrls, $descLinks);
        $this->storeLinks($allLinks);
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
            array_push($eaLinks, array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$value,'status'=>'NEW'));
        }
        return $eaLinks;
    }

    function processCitationUrl($sXml)
    {
        $cUrls = array();
        $ro_id = $this->ro->id;
        $ds_id = $this->ro->data_source_id;
        $citation_metadata = $sXml->xpath('//ro:citationMetadata');
        foreach ($citation_metadata AS $cm)
        {
            $type = 'citation_metadata_url';
            $value = (string)$cm->url[0];
            array_push($cUrls, array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$value,'status'=>'NEW'));
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
            $regex = '/https?\:\/\/[^\" ]+/i';
            preg_match_all($regex, html_entity_decode($desc), $matches);
            $type = 'description_link';
            foreach($matches[0] as $url){
                array_push($descLinks, array('registry_object_id'=>$ro_id, 'data_source_id'=>$ds_id,'link_type'=>$type,'link'=>$url,'status'=>'NEW'));
            }
        }
        return $descLinks;
    }

    function storeLinks($linksArray)
    {
        $this->db->where(array('registry_object_id' => $this->ro->id));
        $this->db->delete('registry_object_links');
        foreach($linksArray as $link){
            $this->db->insert('registry_object_links',$link);
        }
    }
}

