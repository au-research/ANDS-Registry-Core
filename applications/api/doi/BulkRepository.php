<?php


namespace ANDS\API\DOI;


class BulkRepository
{
    public static function getByRequestID($id)
    {
        return Bulk::where('bulk_id', $id);
    }

    public static function addBulk($data)
    {
        return Bulk::create($data);
    }

    public static function hasBulkRequestID($id)
    {
        $count = Bulk::where('bulk_id', $id)->count();
        return $count > 0 ? true : false;
    }
}