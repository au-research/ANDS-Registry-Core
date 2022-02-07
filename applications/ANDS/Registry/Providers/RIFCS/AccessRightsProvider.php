<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\MetadataProvider;
use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;

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
            'access_methods_ss' => static::getAccessMethods($record)
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

        if ($rights = LicenceProvider::get($record)) {
            foreach($rights as $right) {
                if (isset($right['accessRights_type']) && in_array($right['accessRights_type'], $include_rights_type))
                    $access_rights = $right['accessRights_type'];
            }
        }

        /* determine access rights as open if there is a direct download */
        if(AccessProvider::getDirectDownload($record,MetadataProvider::get($record))) $access_rights = 'open';

        return $access_rights;
    }
}