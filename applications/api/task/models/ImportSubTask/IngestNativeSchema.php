<?php

namespace ANDS\API\Task\ImportSubTask;

use \ANDS\Registry\Versions as Versions;
use \ANDS\Registry\Schema;
use \ANDS\Registry\ContentProvider\ContentProvider;
use \ANDS\RegistryObject\RegistryObjectVersion;
use \ANDS\Repository\RegistryObjectsRepository;
use \ANDS\Repository\DataSourceRepository;


class IngestNativeSchema extends ImportSubTask
{
    protected $requirePayload = true;
    public $title = "INGESTING NATIVE CONTENT RECORDS";
    protected $data_source = null;
    protected $payloadSource = "original";
    private $contentProvider = null;

    public function run_task()
    {
        $this->data_source = DataSourceRepository::getByID($this->parent()->dataSourceID);

        $providerType = $this->data_source->getDataSourceAttribute('provider_type');
        $harvestMethod = $this->data_source->getDataSourceAttribute('harvest_method');


        $this->contentProvider = ContentProvider::getProvider($providerType['value'], $harvestMethod['value']);

        // couldn't find content handler for datasource
        if($this->contentProvider == null){
            $this->log("No native data handler for harvest");
            return;
        }

        $fileExtension = $this->contentProvider->getFileExtension();

        if (!$this->parent()->hasPayloadExtension($fileExtension)) {
            $this->log("No native (". $fileExtension .") schema exists");
            return;
        }

        $payloads = $this->parent()->loadPayload($fileExtension)->getPayloads();
        $multiplePayloads = count($payloads) > 1 ? true : false;

        $payloadCounter = 0;
        foreach ($this->parent()->getPayloads() as $payloadIndex => $payload) {

            $payloadContent = $payload->getContentByStatus($this->payloadSource);
            if ($payloadContent === null) {
                $this->addError("No Original content were found for ". $payload->getPath());
                break;
            }
            $this->contentProvider->loadContent($payloadContent);
            $nativeObjects = $this->contentProvider->getContent();

            foreach ($nativeObjects as $nativeObject){
                $success = static::insertNativeObject($nativeObject, $this->parent()->dataSourceID);
                if($success)
                    $payloadCounter += 1;
            }

            if ($multiplePayloads) {
                $this->updateProgress(
                    $payloadCounter, count($payloads),
                    "Processed Native ($payloadCounter/".count($payloads).") " . $payloadIndex
                );
            }
        }

        $this->parent()->updateHarvest([
            "importer_message" => "Records Created/Updated: ".$payloadCounter
        ]);
        $this->parent()->setTaskData("NativeObjectsCreated", $payloadCounter);

    }



    /*Insert a record versions
     *
     * @param nativeObject DomElement
     *
     */
    public static function insertNativeObject($nativeObject, $dataSourceID)
    {

        $identifiers = $nativeObject['identifiers'];
        $nativeObject['nameSpaceURI'];
        $data = $nativeObject['data'];
        $hash = $nativeObject['hash'];
        if (sizeof($identifiers) == 0) {
            echo "Couldn't determine Identifiers so quiting";
            return false;
        }

        $schema = Schema::get($nativeObject['nameSpaceURI']);

        $registryObjects = RegistryObjectsRepository::getRecordsByIdentifier($identifiers, $dataSourceID);
        if($registryObjects )
        {
            $recordIDs = collect($registryObjects)->pluck('registry_object_id')->toArray();
            $success = false;
            foreach ($recordIDs as $id) {
                $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $id)->get()->pluck('version_id')->toArray();
                $existing = null;
                if (count($altVersionsIDs) > 0) {
                    $existing = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $schema->id)->first();
                }
                $success = true;
                if (!$existing) {
                    $version = Versions::create([
                        'data' => $data,
                        'hash' => $hash,
                        'origin' => 'HARVESTER',
                        'schema_id' => $schema->id,
                    ]);
                    RegistryObjectVersion::firstOrCreate([
                        'version_id' => $version->id,
                        'registry_object_id' => $id
                    ]);
                } elseif ($hash != $existing->hash) {
                    $existing->update([
                        'data' => $data,
                        'hash' => $hash
                    ]);
                }
            }
            return $success;
        }
        return false;

    }

}