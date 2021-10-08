#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

restore_error_handler();
restore_exception_handler();
date_default_timezone_set('UTC');

use Symfony\Component\Console\Application;

if (!file_exists('.env')) {
    die("Installation incompleted. .env file missing");
}
$dotenv = new Dotenv\Dotenv(__DIR__);
$result = $dotenv->load();

$application = new Application();

$application->add(new \ANDS\Commands\ConceptsCommand());
$application->add(new \ANDS\Commands\RegistryObject\RegistryObjectGetCommand());
$application->add(new \ANDS\Commands\DOISyncCommand());
$application->add(new \ANDS\Commands\RegistryObject\RegistryObjectSyncCommand());
$application->add(new \ANDS\Commands\RegistryObject\RegistryObjectProcessCommand());
$application->add(new \ANDS\Commands\RegistryObject\RegistryObjectUpdateStatsCommand());
$application->add(new \ANDS\Commands\SyncRecordWorkerRedisCommand());
$application->add(new \ANDS\Commands\RunScriptCommand());
$application->add(new \ANDS\Commands\ExportCommand());
$application->add(new \ANDS\Commands\Graph\GraphGenerate());
$application->add(new \ANDS\Commands\Export\ExportCSV());
$application->add(new \ANDS\Commands\DataSource\DataSourceProcessCommand());
$application->add(new \ANDS\Commands\ClearCacheCommand());
$application->add(new \ANDS\Commands\WarmCacheCommand());
$application->add(new \ANDS\Commands\Export\ExportRoles());

$application->run();