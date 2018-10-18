<?php


namespace ANDS\Commands\Script;


use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
use ANDS\Registry\Providers\DCI\DCI;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessDCI extends GenericScript implements GenericScriptRunnable
{
    protected $availableParams = ["all"];

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

        $reports = [];
        $progressBar = new ProgressBar($this->getOutput(), $unchecked->count());
        foreach ($unchecked->pluck('registry_object_id') as $id) {
            try {
                $record = RegistryObjectsRepository::getRecordByID($id);
                if ($record) {
                    $reports[$record->id] = DataCitationIndexProvider::process($record);
                } else {
                    DCI::where("registry_object_id", $id)->delete();
                    $this->log("Record $id does not exist. Deleted relevant DCI doc");
                }
            } catch (\Exception $e) {
                $this->log("Error processing $id");
            }
            $progressBar->advance(1);
        }
        $progressBar->finish();

        $this->report($reports);

        return;
    }


}