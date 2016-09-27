<?php


namespace ANDS\API\DOI;


/**
 * Class BulkRepository
 * @package ANDS\API\DOI
 */
class BulkRepository
{
    /**
     * Return all Bulk why request ID
     *
     * @param $id
     * @return mixed
     */
    public static function getByRequestID($id)
    {
        return Bulk::where('bulk_id', $id);
    }

    /**
     * Add a new Bulk
     *
     * TODO: validation of Bulk data
     * @param $data
     * @return Bulk
     */
    public static function addBulk($data)
    {
        return Bulk::create($data);
    }

    /**
     * Return if a Bulk ID has any request
     *
     * @param $id
     * @return bool
     */
    public static function hasBulkRequestID($id)
    {
        $count = Bulk::where('bulk_id', $id)->count();
        return $count > 0 ? true : false;
    }

}