<?php

namespace ANDS\Registry\API\Controller;

use ANDS\DataSource;
use ANDS\Mycelium\MyceliumDataSourcePayloadProvider;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Registry\API\Middleware\IPRestrictionMiddleware;
use ANDS\Repository\DataSourceRepository;
use ANDS\Util\Config;

class DataSourcesMiscController extends HTTPController
{
    public function getMyceliumPayload($id) {
        $dataSource = DataSource::find($id);
        return MyceliumDataSourcePayloadProvider::get($dataSource);
    }

    public function postMyceliumPayload($id) {
        $this->middlewares([IPRestrictionMiddleware::class]);
        $dataSource = DataSourceRepository::getByID($id);
        $client = new MyceliumServiceClient(Config::get('mycelium.url'));
        $client->updateDataSource($dataSource);
        return MyceliumDataSourcePayloadProvider::get($dataSource);
    }
}