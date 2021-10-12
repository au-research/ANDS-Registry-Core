<?php
namespace ANDS\Registry\API\Controller;


use ANDS\Repository\DataSourceRepository;

class DataSourcesLogController
{
    public function index($dsAny = null)
    {
        $dataSource = DataSourceRepository::getByAny($dsAny);
        return $dataSource->dataSourceLog;
    }
}