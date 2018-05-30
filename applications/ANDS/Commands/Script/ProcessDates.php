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
        $unchecked = RegistryObject::whereNull('modified_at');
        $progressBar = new ProgressBar($this->getOutput(), $unchecked->count());
        $unchecked->chunk(2000, function($records) use ($progressBar){
            foreach ($records as $record) {
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