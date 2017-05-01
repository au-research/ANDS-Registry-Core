<?php


namespace ANDS\Commands\Script;


use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\Registry\Providers\ScholixProvider;
use ANDS\RegistryObject;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessScholix extends GenericScript implements GenericScriptRunnable
{
    protected $availableParams = ["all", "clean"];

    public function run()
    {
        $params = $this->getInput()->getOption('params');
        if (!$params) {
            $this->log("You have to specify a param: available: ". implode('|', $this->availableParams), "info");
            return;
        }

        switch ($params) {
            case "all":
                $this->log("Processing all published");
                $this->processPublished();
                break;
            case "clean":
                $this->log("Cleanning Scholix");
                $this->cleanScholix();
                break;
            default:
                $this->log("Undefined params. Provided $params");
                break;
        }
    }

    private function processPublished()
    {
        $unchecked = RegistryObject::where('class', 'collection')
            ->whereIn('type', ['dataset', 'collection'])
            ->where('status', 'PUBLISHED');

        $progressBar = new ProgressBar($this->getOutput(), $unchecked->count());
        foreach ($unchecked->get() as $record) {
            $progressBar->advance(1);
            ScholixProvider::process($record);
        }
        $progressBar->finish();
        return;
    }

    private function cleanScholix()
    {
        foreach (Scholix::all() as $scholix) {
            $record = RegistryObject::find($scholix->registry_object_id);
            if (!$record || !$record->isPublishedStatus()) {
                $this->log("Deleted {$scholix->id} because {$scholix->registry_object_id} is DELETED");
            }
        }
        $this->log("Finished");
    }
}