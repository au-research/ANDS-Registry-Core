<?php


namespace ANDS\Commands;


use ANDS\Registry\API\Controller\RecordsGraphController;
use ANDS\RegistryObject;
use ANDS\Util\Config;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class WarmCacheCommand extends ANDSCommand
{
    protected static $objectChunkSize = 10000;

    protected function configure()
    {
        $config = Config::get('app.cache');
        $this->driver = $config['default'];

        /**
         * cache:warm graph
         * TODO cache:warm solr|search
         */

        $this
            ->setName('cache:warm')
            ->setDescription('Warm the default cache for various things')
            ->setHelp("This command allows you to warm the cache")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 2 * ONE_HOUR);

        $this->setUp($input, $output);

        return $this->timedActivity("Warming Graph Cache", function() {
            $this->warmGraphCache();
        });
    }

    private function warmGraphCache()
    {
        $records = RegistryObject::where('status', 'PUBLISHED')->orderBy('registry_object_id');

        $progressBar = new ProgressBar($this->getOutput(), $records->count());
        $records->chunk(static::$objectChunkSize, function($records) use ($progressBar) {
            foreach ($records as $record) {
                /* @var $record RegistryObject */
                try {
                    if ($this->isVerbose()) {
                        $this->log("Processing {$record->id}");
                    }
                    (new RecordsGraphController())->index($record->id);
                } catch (\Exception $e) {
                    $this->log("Failed warming cache for record {$record->id}: {$e->getMessage()}", "error");
                }
                $progressBar->advance(1);
            }
        });
        $progressBar->finish();
        $this->log("\nGraph cache warmed for {$records->count()} records", "info");
    }
}