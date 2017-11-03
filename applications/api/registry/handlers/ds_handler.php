<?php

namespace ANDS\API\Registry\Handler;
use ANDS\DataSource;
use ANDS\DataSource\DataSourceLog;
use ANDS\DataSource\Harvest;
use ANDS\DataSourceAttribute;
use ANDS\Payload;
use ANDS\Registry\API\Router;
use ANDS\Registry\Importer;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use \Exception as Exception;
use ANDS\Registry\Providers\ServiceDiscovery\ServiceDiscovery as ServiceDiscoveryProvider;

/**
 * Class DatasourcesHandler
 * @package ANDS\API\Registry\Handler
 */
class DsHandler extends Handler
{
    public function handle()
    {
        $this->getParentAPI()->providesOwnResponse();
        $this->getParentAPI()->outputFormat = "application/json";

        $router = new Router('/api/registry/');

        // RESOURCE ds/:id
        $router->resource('ds', 'DataSourcesController');

        // RESOURCE ds/:id/attributes
        $router->resource('ds/(\w+)/attributes', 'DataSourcesAttributesController');

        // GET ds/:id/log
        $router->get('ds/(\w+)/log', 'DataSourcesLogController@index');

        // GET ds/:id/harvest
        $router->get('ds/(\w+)/harvest', 'DataSourcesHarvestController@index');

        // PUT|POST ds/:id/harvest
        $router->route(['put', 'post'], 'ds/(\w+)/harvest', 'DataSourcesHarvestController@triggerHarvest');

        // DELETE ds/:id/harvest
        $router->route(['delete'], 'ds/(\w+)/harvest', 'DataSourcesHarvestController@stopHarvest');

        // PUT ds/:id/services
        $router->route(['put', 'post'], 'ds/(\w+)/services', 'DataSourcesServiceController@index');

        // standard resources
        $router->resource("ds/(\w+)/records", 'DataSourcesRecordsController');
        $router->route(['delete'], 'ds/(\w+)/records', 'DataSourcesRecordsController@delete');

        return $this->format($router->execute());
    }

    public function format($data)
    {
        return json_encode($data);
    }
}