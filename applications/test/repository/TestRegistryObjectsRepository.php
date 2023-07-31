<?php

namespace ANDS\Test;

use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Test\UnitTest;

class TestRegistryObjectsRepository extends UnitTest
{
    public function test_getRecordsByHarvestID()
    {
        $ids = RegistryObjectsRepository::getRecordsByHarvestID("2598846DF0D24B1355A8FA9816BD6CB84D246A6C", 210);
        $this->assertGreaterThan(sizeof($ids), -1);
    }

    public function test_getRecordsByDifferentHarvestID()
    {
        $ids = RegistryObjectsRepository::getRecordsByDifferentHarvestID("2598846DF0D24B1355A8FA9816BD6CB84D246A6C", 210);
        $this->assertGreaterThan(sizeof($ids), 100);
    }
}