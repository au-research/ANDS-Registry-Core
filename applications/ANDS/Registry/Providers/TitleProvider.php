<?php


namespace ANDS\Registry\Providers;


use ANDS\Util\XMLUtil;

class TitleProvider implements RIFCSProvider
{

    public static function get($rifcs)
    {

    }

    public static function create()
    {
        return new static;
    }
}