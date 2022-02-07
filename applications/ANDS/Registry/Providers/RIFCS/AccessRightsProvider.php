<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;


class AccessRightsProvider implements RIFCSProvider
{
    /**
     * Process all the available access_rights
     * @param RegistryObject $record
     */
    public static function process(RegistryObject $record)
    {
        // TODO: Implement.
    }

    /**
     * Process all the available access_rights
     * @param RegistryObject $record
     */
    public static function get(RegistryObject $record)
    {
        // TODO: Implement.
    }

    /**
     * Obtain an associative array for the indexable fields
     *
     * @param RegistryObject $record
     * @return array
     */
    public static function getIndexableArray(RegistryObject $record) {

        return [
            'access_rights' => static::getAccessRights($record),
            'access_methods_ss' => static::getAccessMethods($record),
            'license_class' =>  static::getLicenceClass($record)
        ];
    }

    public static function getAccessMethods(RegistryObject $record){
        $accessMethods = AccessProvider::get($record);
        $access_methods_ss = array_keys($accessMethods);
        return $access_methods_ss;
    }

    public static function getAccessRights(RegistryObject $record){
        $access_rights = '';

        // Every collection has an access_right
        if ($record->class == 'collection') {
            $access_rights = 'Other';
        }

        //if there's a secret tag of SECRET_TAG_ACCESS_OPEN/CONDITIONAL/RESTRICTED defined in constants, assign access_rights to open
        $tags = TagProvider::get($record);

        foreach($tags as $tag){
            switch($tag['tag']) {
                case RegistryObject\Tag::$SECRET_TAG_ACCESS_OPEN;
                    $access_rights = 'open';
                    break;
                case RegistryObject\Tag::$SECRET_TAG_ACCESS_CONDITIONAL;
                    $access_rights = 'conditional';
                    break;
                case RegistryObject\Tag::$SECRET_TAG_ACCESS_RESTRICTED;
                    $access_rights = 'restricted';
                    break;
            }
        }

        //determine the access_rights from rifcs rights or description elements
        $include_rights_type = array('open','restricted','conditional');

        if ($rights = self::processLicence($record)) {
            foreach($rights as $right) {
                if (isset($right['accessRights_type']) && in_array($right['accessRights_type'], $include_rights_type))
                    $access_rights = $right['accessRights_type'];
            }
        }

        /* determine access rights as open if there is a direct download */
        if(AccessProvider::getDirectDownload($record,MetadataProvider::get($record))) $access_rights = 'open';

        return $access_rights;
    }

    public static function processLicence(RegistryObject $record){

        $rights = array();
        $recordData = $record->getCurrentData();
        $registryObjectsElement = XMLUtil::getSimpleXMLFromString($recordData->data);

        foreach ($registryObjectsElement->xpath('//ro:'.$record->class.'/ro:rights') AS $theRights)
        {
            $right = array();
            foreach($theRights as $key=>$theRight)
            {
                $right['value']= (string)$theRight;
                if((string)$theRight['rightsUri']!='') $right['rightsUri'] = (string)$theRight['rightsUri'];
                $right['type'] = (string)$key;
                if($right['type']=='licence')
                {
                    if((string)$theRight['type']!='')
                    {
                        $right['licence_type'] = (string)$theRight['type'];
                    }else{
                        $right['licence_type'] = 'Unknown';
                    }

                    $right['licence_group'] = self::getLicenceGroup($right['licence_type']);
                    if($right['licence_group']=='') $right['licence_group'] = 'Unknown';
                }
                if($right['type']=='accessRights')
                {
                    if(trim((string)$theRight['type'])!='')
                    {
                        $right['accessRights_type'] = (string)$theRight['type'];
                    }
                }
                $rights[] = $right;
                unset($right);
            }

        }

        foreach ($registryObjectsElement->xpath('//ro:'.$record->class.'/ro:description') AS $theRightsDescription)
        {
            if($theRightsDescription['type']=='rights'||$theRightsDescription['type']=='accessRights')
            {
                $right = array();
                $right['value']= html_entity_decode((string)$theRightsDescription);
                $right['type'] = (string)$theRightsDescription['type'];

                if(self::checkRightsText($right['value']))
                {
                    $right['licence_group'] = self::checkRightsText($right['value']);
                }

                $rights[] = $right;
            }

        }
        return $rights;
    }


    // Temporary workaround for storing "groupings" of licence identifiers
    // XXX: Long term solution should use a vocabulary service (such as ANDS's)
    private static $licence_groups = array(
        "GPL" => "Open Licence",
        "CC-BY-SA" => "Open Licence",
        "CC-BY-ND" => "Non-Derivative Licence",
        "CC-BY-NC-SA" => "Non-Commercial Licence",
        "CC-BY-NC-ND" => "Non-Derivative Licence",
        "CC-BY-NC" => "Non-Commercial Licence",
        "CC-BY" => "Open Licence",
        "CC0" => "Open Licence",
        "CSIRO Data Licence" => "Non-Commercial Licence",
        "AusGoalRestrictive" => "Restrictive Licence",
        "NoLicence" => "No Licence"

    );

    private static function getLicenceGroup($licence_type)
    {
        if (isset(self::$licence_groups[(string)$licence_type]))
        {
            return self::$licence_groups[(string)$licence_type];
        }
        else
        {
            return '';
        }

    }

    private static function checkRightsText($value)
    {

        if((str_replace("http://creativecommons.org/licenses/by/","",$value)!=$value)||(str_replace("http://creativecommons.org/licenses/by-sa/","",$value)!=$value))
        {
            return "Open Licence";
        }
        elseif((str_replace("http://creativecommons.org/licenses/by-nc/","",$value)!=$value)||(str_replace("http://creativecommons.org/licenses/by-nc-sa/","",$value)!=$value))
        {
            return "Non-Commercial Licence";
        }
        elseif((str_replace("http://creativecommons.org/licenses/by-nd/","",$value)!=$value)||(str_replace("http://creativecommons.org/licenses/by-nc-nd/","",$value)!=$value))
        {
            return "Non-Derivative Licence";
        }
        else
        {
            return false;
        }
    }

    public static function getLicenceClass(RegistryObject $record){
        $licence_class = '';
        if ($rights = self::processLicence($record)) {
            foreach($rights as $right) {
                if (isset($right['licence_group'])) {
                    $licence_class = strtolower($right['licence_group']);
                    if ($licence_class == 'unknown') $json['license_class'] = 'Other';
                }
            }
        }
        return $licence_class;
    }
}