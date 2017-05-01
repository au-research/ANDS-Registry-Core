<?php


namespace ANDS\Commands\Script;

use ANDS\Registry\Providers\TitleProvider;
use ANDS\RegistryObject;

class ProcessTitles extends GenericScript implements GenericScriptRunnable
{
    protected $availableParams = ["all", "255"];
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
            case "255":
                $this->log("Processing titles longer than 255");
                $this->longerThan255();
                break;
            default:
                $this->log("Undefined params. Provided $params");
                break;
        }
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

    private function longerThan255()
    {
        $registryObjects = RegistryObject::whereRaw('LENGTH(title) > 255');
        $this->log("Processing {$registryObjects->count()} records");
        foreach ($registryObjects->get() as $ro) {
            $this->updateTitle($ro);
        }
        $this->log("Done", "info");
    }

    private function updateTitle(RegistryObject $ro)
    {
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