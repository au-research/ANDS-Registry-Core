<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 11/2/19
 * Time: 11:27 AM
 */

namespace ANDS\Registry\API\Controller;

use ANDS\Registry\Versions;
use ANDS\Repository\RegistryObjectsRepository;
use Exception;
use DOMDocument;

class RecordsVersionsController extends HTTPController
{
    /**
     * @param $id
     * @return mixed
     */
    public function index($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $versions = $record->versions;
        return $versions;
    }

    /**
     * Check if the version belongs to the recordID
     *
     * @param $recordID
     * @param $versionID
     * @return mixed
     */
    public function show($recordID, $versionID)
    {

        $version = Versions::find($versionID);
        $data = $version->data;
        if($data != '' && $data[0] == '<'){
           // try{

            //   $dom = new DOMDocument();
            ////   $dom->loadXML($version->data);
            $this->printXML($version->data);
            //}
            //catch (Exception $e){
                //not XML
            //}
        }else{
            //try{
                header('Content-type: application/json');
           //     $jsonData = json_encode($version->data);
                echo $version->data;
                die();
           // }
           // catch (Exception $e){
                // not JSON
          //  }
        }
        //echo $version->data;
        //die();

    }
}