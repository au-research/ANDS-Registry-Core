<?php

namespace ANDS\API\Repository;

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

class DataciteClientRespository
{

    private $client = null;

    public function getFirst()
    {
        return DataciteClient::first();
    }

    public function getByID($id)
    {
        return DataciteClient::find($id);
    }

    /**
     * Authenticate a client based on their shared secret and/or their ipAddress
     *
     * @param $appID
     * @param null $sharedSecret
     * @param null $ipAddress
     * @return bool
     */
    public function authenticate($appID, $sharedSecret = null, $ipAddress = null)
    {
        $client = DataciteClient::where('app_id', $appID)->first();

        if ($client === null) {
            return false;
        }

        if ($client->shared_secret === $sharedSecret) {
            return true;
        }

        return false;
    }

    /**
     * DataciteClientRespository constructor.
     */
    public function __construct()
    {
        require_once(__DIR__.'/../../vendor/autoload.php');

        $dotenv = new Dotenv(__DIR__.'/../');
        $dotenv->load();

        $capsule = new Capsule;
        $capsule->addConnection(
            [
                'driver'    => 'mysql',
                'host'      => getenv("DATABASE_URL"),
                'database'  => 'dbs_dois',
                'username'  => getenv("DATABASE_USERNAME"),
                'password'  => getenv("DATABASE_PASSWORD"),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ], 'default'
        );
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}