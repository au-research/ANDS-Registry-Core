<?php

namespace ANDS\Registry\Providers;

use ANDS\RegistryObject;

interface RIFCSProvider
{
    public static function process(RegistryObject $record);
    public static function get(RegistryObject $record);
}