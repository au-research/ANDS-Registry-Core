<?php


namespace ANDS\DOI;

class SchemaProvider
{
    /**
     * Returns the schema file location
     *
     * @param $theSchema (eg. /kernel-3/metadata.xsd)
     * @return mixed
     */
    public static function getSchema($theSchema)
    {
        return __DIR__."/schema".$theSchema;
    }
}