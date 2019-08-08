<?php

namespace ANDS\DOI\Repository;

use ANDS\DOI\Model\Doi as Doi;
use Illuminate\Database\Capsule\Manager as Capsule;

class DoiRepository
{
    /**
     * @return mixed
     */
    public function getFirst()
    {
        return Doi::first();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getByID($id)
    {
        return Doi::find($id);
    }

    /**
     * @param $doi
     * @param $attributes
     */
    public function doiUpdate($doi, $attributes)
    {
        foreach ($attributes as $key => $value) {
            $doi->$key = $value;
        }
        $doi->save();
    }

    /**
     * @param $attributes
     * @return bool
     */
    public function doiCreate($attributes)
    {
        $doi = new Doi;
        foreach ($attributes as $key => $value) {
            $doi->$key = $value;
        }
        $doi->save();
        return true;
    }

    /**
     * DoiRespository constructor.
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


}