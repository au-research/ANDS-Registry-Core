<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;

interface RegistryContentProvider
{
    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record);

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function get(RegistryObject $record);
}