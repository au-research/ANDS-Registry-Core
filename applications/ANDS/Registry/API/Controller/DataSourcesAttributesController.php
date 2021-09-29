<?php
namespace ANDS\Registry\API\Controller;

use ANDS\Commands\Script\ProcessTitles;
use ANDS\Registry\API\Middleware\IPRestrictionMiddleware;
use ANDS\Registry\API\Request;
use ANDS\Repository\DataSourceRepository;

class DataSourcesAttributesController extends HTTPController implements RestfulController
{

    public function index($dsAny = null)
    {
        $dataSource = DataSourceRepository::getByAny($dsAny);
        return $dataSource->datasourceAttributes;
    }

    public function show($dsAny = null, $name = null)
    {
        $dataSource = DataSourceRepository::getByAny($dsAny);
        return $dataSource->getDataSourceAttribute($name);
    }

    public function update($dsAny = null, $name = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);
        $this->validate(['value']);
        $dataSource = DataSourceRepository::getByAny($dsAny);
        $dataSource->setDataSourceAttribute(
            $name, Request::value('value')
        );
        $attribute = $dataSource->getDataSourceAttribute($name);
        return $attribute;
    }

    public function destroy($dsAny = null, $name = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);
        $dataSource = DataSourceRepository::getByAny($dsAny);
        return $dataSource->removeDataSourceAttribute($name);
    }

    public function add($dsAny = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);
        $this->validate(['attribute', 'value']);
        $dataSource = DataSourceRepository::getByAny($dsAny);

        $existing = $dataSource->getDataSourceAttribute(Request::value('attribute'));
        if ($existing) {
            $name = Request::value('attribute');
            throw new \Exception("Attribute $name already exist.");
        }

        $attribute = $dataSource->setDataSourceAttribute(
            Request::value('attribute'), Request::value('value')
        );

        return $attribute;

    }
}