<?php


namespace ANDS\Repository;


use ANDS\DataSource;

class DataSourceRepository
{
    public static function getByKey($key)
    {
        return DataSource::where('key', $key)->first();
    }

    /**
     * @param $data_source_id
     * @return DataSource
     */
    public static function getByID($data_source_id)
    {
        return DataSource::where('data_source_id', $data_source_id)->first();
    }
}