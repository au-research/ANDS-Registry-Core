<?php


namespace ANDS\Commands\Script;


use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\RegistryObject;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessDates extends GenericScript implements GenericScriptRunnable
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
                $this->all();
                break;
            default:
                $this->log("Undefined params. Provided $params");
                break;
        }
    }

    private function all()
    {
        $records = RegistryObject::whereNull('modified_at')->orderBy('registry_object_id');
        $progressBar = new ProgressBar($this->getOutput(), $records->count());
        $records->chunk(1000, function($records) use ($progressBar){
            foreach ($records as $record) {
                /* @var $record RegistryObject */
                try {
                    DatesProvider::process($record);
                } catch (\Exception $e) {
                    $this->log("Failed {$record->id}: {$e->getMessage()}", "error");
                }
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();
        return;
    }
}