<?php
namespace ANDS\Registry\API\Controller;


use ANDS\Registry\API\Request;
use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
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

    public function scholix($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $scholix = \ANDS\Registry\Providers\ScholixProvider::get($record);

        $wt = Request::get('wt', 'xml');
        switch ($wt) {
            case "json":
                return $scholix->toJson();
            case "xml":
                $this->printXML($scholix->toXML());
//                return $scholix->toXML();
            case "oai":
                $this->printXML($scholix->toOAI());
//                return $scholix->toOAI();
            default:
                return $scholix->toArray();
        }
    }

    public function dci($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $dci = DataCitationIndexProvider::get($record);
        $this->printXML($dci);
    }

    public function dciValidate($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $dci = DataCitationIndexProvider::get($record);
        return DataCitationIndexProvider::validate($dci);
    }

    public function orcidValidate($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        return ORCIDProvider::getORCID($record, new ORCIDRecord())->validate();
    }

    private function printXML($xml)
    {
        header('Content-type: application/xml');
        echo $xml;
        die();
    }
}