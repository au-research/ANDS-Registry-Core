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
        $bulk = new Bulk;
        foreach ($data as $key=>$value) {
            $bulk->$key = $value;
        }
        $bulk->save();
    }

    public static function hasBulkRequestID($id)
    {
        $count = Bulk::where('bulk_id', $id)->count();
        return $count > 0 ? true : false;
    }
}