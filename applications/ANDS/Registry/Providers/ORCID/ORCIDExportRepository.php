<?php
namespace ANDS\Registry\Providers\ORCID;


class ORCIDExportRepository
{
    public static function getByRegistryObjectID($roID)
    {
        return ORCIDExport::where('registry_object_id', $roID)->get();
    }

    public static function getByORCIDID($id)
    {
        return ORCIDExport::where('orcid_id', $id)->get();
    }
}