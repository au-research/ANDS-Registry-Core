<?php


use ANDS\Repository\RegistryObjectsRepository;

class RegistryTestClass extends PHPUnit_Framework_TestCase
{
    protected $requiredKeys = [];

    public function setUp()
    {
        restore_error_handler();
        foreach ($this->requiredKeys as $key) {
            $record = RegistryObjectsRepository::getPublishedByKey($key);
            if ($record === null) {
                $this->markTestSkipped("The record with $key is not available. Skipping tests...");
            }
        }
    }
}