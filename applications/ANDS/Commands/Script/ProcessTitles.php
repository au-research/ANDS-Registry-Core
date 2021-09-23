<?php


namespace ANDS\Commands\Script;

use ANDS\Registry\Providers\RIFCS\CoreMetadataProvider;
use ANDS\Registry\Providers\RIFCS\TitleProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class ProcessTitles extends GenericScript implements GenericScriptRunnable
{
    protected $availableParams = ["published", "200", "all", "null"];
    public function run()
    {
        $params = $this->getInput()->getOption('params');
        if (!$params) {
            $this->log("You have to specify a param: available: ". implode('|', $this->availableParams), "info");
            return;
        }

        switch ($params) {
            case "published":
                $this->log("Processing all published titles");
                $this->processPublished();
                break;
            case "200":
                $this->log("Processing titles longer than 200");
                $this->longerThan200();
                break;
            case "all":
                $this->log("Processing titles for ALL records");
                $this->processAll();
                break;
            case "null":
                $this->log("Processing all records where title is NULL");
                $this->processNull();
                break;
            default:
                $this->log("Undefined params. Provided $params");
                break;
        }
    }

    private function processAll()
    {
        $registryObjects = RegistryObject::all();
        $total = count($registryObjects);
        $this->log("Processing {$total} records");
        foreach ($registryObjects as $ro) {
            $this->updateTitle($ro);
        }
        $this->log("Done", "info");
    }

    private function processPublished()
    {
        $registryObjects = RegistryObject::where("status", "PUBLISHED");
        $this->log("Processing {$registryObjects->count()} records");
        foreach ($registryObjects->get() as $ro) {
            $this->updateTitle($ro);
        }
        $this->log("Done", "info");
    }

    private function processNull()
    {
        $registryObjects = RegistryObject::whereNull("title")->pluck('registry_object_id');
        $this->log("Processing {$registryObjects->count()} records");
        foreach ($registryObjects as $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            CoreMetadataProvider::process($record);
            $this->updateTitle($record);
        }
        $this->log("Done", "info");
    }

    private function longerThan200()
    {
        $registryObjects = RegistryObject::whereRaw('LENGTH(title) > 200');
        $this->log("Processing {$registryObjects->count()} records");
        foreach ($registryObjects->get() as $ro) {
            $this->updateTitle($ro);
        }
        $this->log("Done", "info");
    }

    private function updateTitle(RegistryObject $ro)
    {
        $this->log("Proccessing $ro->id");
        $old = $ro->title;
        TitleProvider::process($ro);
        $new = $ro->title;
        if ($old == $new) {
            $this->log("No change for $ro->id");
        } else {
            $this->log("Title updated for $ro->id. \n old: $old \n new: $new", "info");
            $this->log("-----");
        }
    }
}