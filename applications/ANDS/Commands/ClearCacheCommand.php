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
//    private $driver = null;

    protected function configure()
    {
        $config = Config::get('app.cache');
        $this->driver = $config['default'];

        $this
            ->setName('cache:flush')
            ->setDescription('Flush the default cache')
            ->setHelp("This command allows you to clear the cache")
//            ->addOption('driver', 'd', InputOption::VALUE_OPTIONAL, "driver", $this->driver)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setUp($input, $output);
        Cache::file()->flush();
        $this->log("Cache flushed!");
    }
}