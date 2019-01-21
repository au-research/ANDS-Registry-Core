<?php

namespace ANDS\Registry\Suggestors;

use ANDS\RegistryObject;

interface RegistryObjectSuggestor
{
    /**
     * Results in an array of suggested registry object,
     * each having: [id, title, key, slug, RDAUrl, score]
     * @param RegistryObject $record
     * @return array|null
     */
    public function suggest(RegistryObject $record);
}