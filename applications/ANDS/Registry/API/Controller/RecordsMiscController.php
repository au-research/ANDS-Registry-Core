<?php
namespace ANDS\Registry\API\Controller;


use ANDS\Mycelium\MyceliumImportPayloadProvider;
use ANDS\Registry\API\Request;
use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
use ANDS\Registry\Providers\DublinCore\DublinCoreProvider;
use ANDS\Registry\Providers\ORCID\ORCIDProvider;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Registry\Providers\Quality\QualityMetadataProvider;
use ANDS\Registry\Providers\RIFCS\JsonLDProvider;
use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\Repository\RegistryObjectsRepository;

class RecordsMiscController
{
    public function rifcs($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $xml = $record->getCurrentData()->data;
        $this->printXML($xml);
    }

    public function oai_dc($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $xml = DublinCoreProvider::get($record);
        $this->printXML($xml);
    }

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
        $scholix = \ANDS\Registry\Providers\Scholix\ScholixProvider::get($record);

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


    public function solr_index($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $solr_index = RIFCSIndexProvider::get($record);
        $response = json_encode($solr_index);
        $json_error = json_last_error();
        if($json_error == JSON_ERROR_NONE) {
            return $solr_index;
        }else{
            return "Unable to provide solr doc for record ". $id . " JSON_ERROR_NUMBER:".$json_error;
        }
    }


    /**
     * GET json_ld endpoint for a record
     *
     * @param $id
     * @return mixed
     */
    public function json_ld($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        if($record == null){
            return "Registry Object with ID ". $id . " not found";
        }
        // always use 'process' to get the latest
        $jsonLD = JsonLDProvider::process($record);
        return json_decode($jsonLD);
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

    public function mycelium($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        return MyceliumImportPayloadProvider::get($record);
    }

    public function quality($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        return QualityMetadataProvider::getMetadataReport($record);
    }

    private function printXML($xml)
    {
        header('Content-type: application/xml');
        echo $xml;
        die();
    }
}