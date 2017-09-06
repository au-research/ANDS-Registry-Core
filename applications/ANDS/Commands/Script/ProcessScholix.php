<?php


namespace ANDS\Commands\Script;


use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\Registry\Providers\ScholixProvider;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessScholix extends GenericScript implements GenericScriptRunnable
{
    protected $availableParams = ["all", "clean", "regen"];

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
            case "regen":
                $this->log("Regenerating all scholix");
                $this->regenScholix();
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
                    $reports[$record->id] = ScholixProvider::process($record);
                } else {
                    Scholix::where("registry_object_id", $id)->delete();
                    $this->log("Record $id does not exist. Deleted all Scholix that belongs to that record");
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

    private function regenScholix()
    {
        $ids = Scholix::all()->pluck('registry_object_id')->unique();
        $this->log("Total: {$ids->count()} records");

        $reports = [];
        $progressBar = new ProgressBar($this->getOutput(), $ids->count());
        foreach ($ids as $id) {
            try {
                $record = RegistryObject::find($id);
                if ($record) {
                    $reports[$record->id] = ScholixProvider::process($record);
                } else {
                    Scholix::where("registry_object_id", $id)->delete();
                    $this->log("Record $id does not exist. Deleted all Scholix that belongs to that record");
                }
            } catch (\Exception $e) {
                $this->log("Error processing $id: ". $e->getMessage());
            }
            $progressBar->advance(1);
        }
        $progressBar->finish();

        $this->report($reports);

        return;
    }

    private function report($reports)
    {
        $this->log("\n");
        $total = collect($reports)->pluck('total')->sum();
        $totalUnchanged = collect($reports)->pluck('unchanged')->flatten()->count();
        $totalUpdated = collect($reports)->pluck('updated')->flatten()->count();
        $totalCreated = collect($reports)->pluck('created')->flatten()->count();
        $totalDeleted = collect($reports)->pluck('deleted')->flatten()->count();
        $this->log("Total Scholix Documents: $total");
        $this->log("Total Unchanged: ". $totalUnchanged);
        $this->log("Total Updated: ". $totalUpdated);
        $this->log("Total Created: ". $totalCreated);
        $this->log("Total Deleted: ". $totalDeleted);
    }

    private function cleanScholix()
    {
        foreach (Scholix::all() as $scholix) {
            $record = RegistryObject::find($scholix->registry_object_id);
            if (!$record || !$record->isPublishedStatus()) {
                $scholix->delete();
                $this->log("Deleted {$scholix->id} because {$scholix->registry_object_id} is DELETED");
            }
        }
        $this->log("Finished");
    }
}