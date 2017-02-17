<?php


namespace ANDS\Registry\Providers;


use ANDS\RegistryObject;

interface RegistryContentProvider
{
    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $object
     * @return mixed
     */
    public static function process(RegistryObject $object);

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $object
     * @return mixed
     */
    public static function get(RegistryObject $object);
}