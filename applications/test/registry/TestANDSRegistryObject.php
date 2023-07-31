<?php

namespace ANDS\Test;

use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;

class TestANDSRegistryObject extends UnitTest
{
    public function testIsManualEntered()
    {
        $record = RegistryObjectsRepository::getByKeyAndStatus("http://anu.edu.au/anudc:4917", "DRAFT");
        $this->assertTrue($record->isManualEntered());
    }

    public function testIsNotManualEntered()
    {
        $record = RegistryObjectsRepository::getByKeyAndStatus("http://anu.edu.au/anudc:4917", "PUBLISHED");
        $this->assertFalse($record->isManualEntered());
    }
}