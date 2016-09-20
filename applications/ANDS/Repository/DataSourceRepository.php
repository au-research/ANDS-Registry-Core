<?php


namespace ANDS\Repository;


use ANDS\DataSource;

class DataSourceRepository
{
    public static function getByKey($key)
    {
        return DataSource::where('key', $key)->first();
    }
}