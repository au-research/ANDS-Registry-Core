<?php

namespace ANDS\Registry\API\Controller;


use ANDS\Registry\API\Middleware\IPRestrictionMiddleware;
use ANDS\Repository\DataSourceRepository;

class DataSourcesHarvestController extends HTTPController
{
    public function index($dsAny = null)
    {
        $dataSource = DataSourceRepository::getByAny($dsAny);
        $dataSource->load('harvest');
        return $dataSource->harvest;
    }

    public function triggerHarvest($dsAny = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);
        $dataSource = DataSourceRepository::getByAny($dsAny);
        $dataSource->startHarvest();
        return $dataSource->harvest;
    }

    public function stopHarvest($dsAny = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);
        $dataSource = DataSourceRepository::getByAny($dsAny);
        $dataSource->stopHarvest();
        return $dataSource->harvest;
    }
}