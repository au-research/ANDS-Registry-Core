<?php


namespace ANDS\Commands\Script;


use ANDS\Registry\Providers\LinkProvider;
use ANDS\RegistryObject;
use Symfony\Component\Console\Helper\ProgressBar;

class ProcessLinksScript extends GenericScript
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
                return $this->timedActivity("Processing all Published Records", function() {
                   return $this->processPublished();
                });
                break;
            default:
                $this->log("Undefined params. Provided $params");
                break;
        }
    }

    private function processPublished()
    {
        $records = RegistryObject::where('status', 'PUBLISHED');

        $progressBar = new ProgressBar($this->getOutput(), $records->count());

        $records->chunk(2000, function($records) use ($progressBar) {
            foreach ($records as $record) {
                /* @var $record RegistryObject */

                try {
                    LinkProvider::process($record);
                } catch (\Exception $e) {
                    $this->log("Failed process links for record {$record->id}: {$e->getMessage()}", "error");
                }

                $progressBar->advance(1);
            }
        });
    }
}