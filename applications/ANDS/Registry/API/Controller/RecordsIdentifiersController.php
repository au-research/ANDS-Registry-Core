<?php

namespace ANDS\Registry\API\Controller;


use ANDS\RegistryObject\Identifier;

class RecordsIdentifiersController
{
    public function index($id)
    {
        $identifiers =  Identifier::where('registry_object_id', $id);
        if ($type = request('type')) {
            $identifiers = $identifiers->where('identifier_type', $type);
        }
        return $identifiers->get();
    }
}