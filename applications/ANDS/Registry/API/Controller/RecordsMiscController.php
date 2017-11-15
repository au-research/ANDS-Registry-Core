<?php
namespace ANDS\Registry\API\Controller;


use ANDS\Registry\Providers\ORCID\ORCIDProvider;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Repository\RegistryObjectsRepository;

class RecordsMiscController
{
    /**
     * GET api/registry/records/:id/orcid
     * Use for debugging purposes
     *
     * @param $id
     */
    public function orcid($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $xml = ORCIDProvider::getORCIDXML($record, new ORCIDRecord());
        $this->printXML($xml);
    }

    private function printXML($xml)
    {
        header('Content-type: application/xml');
        echo $xml;
        die();
    }
}