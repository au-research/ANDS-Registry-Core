<?php

namespace ANDS\DOI\Repository;

use ANDS\DOI\Validator\IPValidator;
use ANDS\DOI\Model\Client as Client;
use ANDS\DOI\Model\ClientPrefixes as ClientPrefixes;
use ANDS\DOI\Model\Prefix as Prefix;
use Illuminate\Database\Capsule\Manager as Capsule;

class ClientRepository
{

    private $message = null;

    /**
     * Create a client
     *
     * @param $params
     * @return Client
     */
    public function create($params)
    {
        $client = new Client;
        $client->fill($params);
        $client->save();

        // update datacite_symbol
        $this->generateDataciteSymbol($client);

        return $client;
    }

    /**
     * Update a client
     *
     * @param $params
     * @return Client
     */
    public function updateClient($params)
    {
        $clientId = $params['client_id'];
        $client = $this->getByID($clientId);
        $client->update($params);
        $client->save();
        return $client;
    }

    /**
     * Returns all Clients
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll()
    {
        return Client::all();
    }

    /**
     * Generate a datacite symbol for the given client
     * ANDS.CENTRE-1
     * ANDS.CENTRE-9
     * ANDS.CENTRE10
     * ANDS.CENTRE99
     * ANDS.C100
     * ANDS.C102
     *
     * @param Client $client
     * @return Client
     */
    public function generateDataciteSymbol(Client $client)
    {
        $prefix = "ANDS.";
        $id = $client->client_id;

        // prefix before the
        if ($id < 100) {
            $prefix .= "CENTRE";
        }

        if ($id < 10) {
            $prefix .= "-";
        } elseif ($id >= 100) {
            // prefix before the ID (new form)
            $prefix .= "C";
        }

        $client->datacite_symbol = $prefix . $id;
        $client->save();

        return $client;
    }

    /**
     * @param $id
     * @return Client
     */
    public function getByID($id)
    {
        return Client::find($id);
    }

    /**
     * @param $appID
     * @return Client
     */
    public function getByAppID($appID)
    {
        //amended where clause to check either app_id or test_app_id of merged prod/test client R29
        return Client::where(function ($query) use ($appID) {
            $query->where('app_id', '=', $appID)
                ->orWhere('test_app_id', '=', $appID);
        })->first();
    }

    /**
     * @param $appID
     * @return Client
     */
    public function getBySymbol($symbol)
    {
        return Client::where(function ($query) use ($symbol) {
            $query->where('datacite_symbol', '=', $symbol);
        })->first();
    }

    /**
     * @param $id
     */
    public function deleteClientById($id)
    {
        $client = static::getByID($id);
        $client->removeClientDomains();
        $client->removeClientPrefixes();

        //soft delete
        $params = array('status'=>'INACTIVE');
        $client->update($params);
        $client->save();
       }

    /**
     * @return mixed
     * find all prefixes in the database that has no clients assigned to
     * the exclude list was added to allow eg: legacy prefixes are not included in result
     */
    public function getUnalocatedPrefixes($mode = 'prod',$excluding = [])
    {
        if($mode == 'test') {
            $is_test = 1;
        }else{
            $is_test = 0;
        }
        $usedPrefixIds = ClientPrefixes::select('prefix_id')->get();
        $prefixes = Prefix::whereNotIn('id', $usedPrefixIds)->whereNotIn("prefix_value", $excluding)->where("is_test",$is_test)->get();
        return $prefixes;
    }

    /**
     * @param array $excluding
     * @return mixed
     * return the first unallocated prefix
     * can be used to randomly assign prefixes to clients
     * eg release 28 when all trusted clients had to get a new prefix
     * the exclude list was added to allow eg: legacy prefixes are not included in result
     */
    public function getOneUnallocatedPrefix($mode = 'prod', $excluding = [])
    {
        if($mode == 'test') {
            $is_test = 1;
        }else{
            $is_test = 0;
        }
        $usedPrefixIds = ClientPrefixes::select('prefix_id')->get();
        return Prefix::whereNotIn('id', $usedPrefixIds)->whereNotIn("prefix_value", $excluding)->where("is_test",$is_test)->first();

    }

    /**
     * @param $pPrefix
     * as its name says
     */
    public function addOrUpdatePrefix($pPrefix){
        $prefix = Prefix::where("prefix_value", $pPrefix['prefix_value'])->first();
        if($prefix)
            $prefix->update($pPrefix);
        else
            $prefix = new Prefix($pPrefix);
        $prefix->save();
    }
    /**
     * Authenticate a client based on their shared secret and/or their ipAddress
     *
     * @param $appID
     * @param null $sharedSecret
     * @param null $ipAddress
     * @param bool $manual
     * @return Client|bool
     */
    public function authenticate(
        $appID,
        $sharedSecret = null,
        $ipAddress = null,
        $manual = false
    ) {
        $test_prefix = false;
        $test_on_prod = false;
        if (substr($appID, 0, 4) == "TEST") {
            $appID = str_replace("TEST", "", $appID);
            $test_prefix = true;
            $test_on_prod = true;
        }

        $client = $this->getByAppID($appID);

        // No Client Exists
        if ($client === null) {
            $this->setMessage("Client does not exists");
            return false;
        }

        //if the client has passed their test app_id we need to set prefix to test prefix
        if ($client->test_app_id == $appID ) {
            $test_prefix = true;
        }

        // Client exists and it's a manual request
        if ($manual) {
            return $client;
        }

        //client exists and has been set to a test account via the app_id make sure that the test prefix is used

        if ($test_prefix) {
            $client['mode'] = "test";
        }

        // if sharedSecret is provided

        //if the test_app_id is being used compare provided shared secret with test_shared_secret
        if ($sharedSecret && $test_prefix && !$test_on_prod) {
            if ($client->test_shared_secret !== $sharedSecret) {
                $this->setMessage("Authentication Failed. Mismatch test shared secret provided");
                return false;
            }
            return $client;
        }elseif($sharedSecret){
            if ($client->shared_secret !== $sharedSecret) {
                $this->setMessage("Authentication Failed. Mismatch shared secret provided");
                return false;
            }
            return $client;
        }

        // ip address matching
        // Need to determine if the obtained ip address is now a comma concatenated string with the proxy ips and use the first address as the real one
        $ipAddresses = explode(",", $ipAddress);
        $ipAddress = trim($ipAddresses[0]);
        if ($ipAddress &&
            IPValidator::validate($ipAddress, $client->ip_address) === false
        ) {
            $this->setMessage("Authentication Failed. Mismatch IP Address. Provided IP Address: " . $ipAddress);
            return false;
        }

        return $client;
    }

    /**
     * ClientRespository constructor.
     * @param $databaseURL
     * @param string $database
     * @param string $username
     * @param string $password
     * @param int $port
     * @internal param string $databasePassword
     */
    public function __construct(
        $databaseURL,
        $database = "dbs_dois",
        $username = "webuser",
        $password = "",
        $port = 3306
    ) {
        $capsule = new Capsule;
        $capsule->addConnection(
            [
                'driver' => 'mysql',
                'host' => $databaseURL,
                'database' => $database,
                'username' => $username,
                'password' => $password,
                'port' => $port,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
            ], 'default'
        );
        $capsule->setAsGlobal();
        $capsule->getConnection('default');
        $capsule->bootEloquent();
    }

    /**
     * @return null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param null $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

}