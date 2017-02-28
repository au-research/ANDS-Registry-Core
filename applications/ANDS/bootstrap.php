<?php

use Illuminate\Database\Capsule\Manager as Capsule;

define('BASEPATH', '/../../');

// require_once the autoload file (again)
require_once __DIR__.'/../../vendor/autoload.php';

// require the constants
require_once __DIR__.'/../../engine/config/constants.php';

// bootstrap the application

// Initialised dbs_registry database
$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../../');
$dotenv->load();
$capsule = new Capsule;
$capsule->addConnection(
    [
        'driver' => 'mysql',
        'host' => getenv("DB_HOSTNAME"),
        'database' => env("DB_DATABASE", "dbs_registry"),
        'username' => getenv("DB_USERNAME"),
        'password' => getenv("DB_PASSWORD"),
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'options'   => array(
            \PDO::ATTR_PERSISTENT => true,
        )
    ], 'default'
);
$capsule->setAsGlobal();
$capsule->bootEloquent();