<?php


use ANDS\Repository\RegistryObjectsRepository;

class RegistryTestClass extends PHPUnit_Framework_TestCase
{
    protected $requiredKeys = [];

    public function setUp()
    {
        restore_error_handler();
        foreach ($this->requiredKeys as $key) {
            $this->ensureKeyExist($key);
        }
    }

    public function ensureKeyExist($key)
    {
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        if ($record === null) {
            $this->markTestSkipped("The record with key: $key is not available. Skipping tests...");
        }

        return $record;
    }

    public function ensureIDExist($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        if ($record === null) {
            $this->markTestSkipped("The record with id: $id is not available. Skipping tests...");
        }

        return $record;
    }
}