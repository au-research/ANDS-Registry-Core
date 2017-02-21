#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

restore_error_handler();

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \ANDS\Commands\ConceptsCommand());

$application->run();