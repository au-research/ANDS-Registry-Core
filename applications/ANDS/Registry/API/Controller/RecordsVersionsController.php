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
        return $this->printXML($version->data);

    }
}