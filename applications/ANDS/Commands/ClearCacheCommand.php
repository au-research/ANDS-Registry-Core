<?php


namespace ANDS\Commands;


use ANDS\Cache\Cache;
use ANDS\Cache\CacheManager;
use ANDS\Util\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends ANDSCommand
{
    private $driver = null;

    protected function configure()
    {
        $this
            ->setName('cache:flush')
            ->setDescription('Flush cache')
            ->setHelp("This command allows you to clear the cache")
            ->addOption('driver', 'd', InputOption::VALUE_OPTIONAL, "driver", $this->driver)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);

        $driver = $input->getOption('driver');
        if (!$driver) {
            $this->log("Driver must be specified with -d");
            return;
        }

        $cache = Cache::driver($driver);
        if (!$cache) {
            $this->log("Driver $driver is not implemented");
        }

        $cache->flush();
        $this->log("Cache $driver flushed!");
    }
}