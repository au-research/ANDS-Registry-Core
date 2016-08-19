<?php

namespace ANDS\API\Repository;

use ANDS\API\Validator\IPValidator;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

class DataciteClientRespository
{

    private $authenticatedClient = null;

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

        // shared secret matching
        if ($sharedSecret &&
            $client->shared_secret === $sharedSecret) {
            $this->setAuthenticatedClient($client);
            return true;
        }

        // ip address matching
        if ($ipAddress &&
            IPValidator::validate($ipAddress, $client->ip_address)) {
            $this->setAuthenticatedClient($client);
            return true;
        }

        return false;
    }

    /**
     * @return null
     */
    public function getAuthenticatedClient()
    {
        return $this->authenticatedClient;
    }

    /**
     * Setting the current authenticated client for this object
     *
     * @param $client
     */
    public function setAuthenticatedClient($client)
    {
        $this->authenticatedClient = $client;
    }

    /**
     * Returns if a client is authenticated
     *
     * @return bool
     */
    public function isClientAuthenticated()
    {
        return $this->getAuthenticatedClient() === null ? false : true;
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
        $capsule->getConnection('default');
        $capsule->bootEloquent();
    }


}