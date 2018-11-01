<?php


use ANDS\DataSource;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;

class RegistryTestClass extends PHPUnit_Framework_TestCase
{
    protected $requiredKeys = [];

    /** @var DataSource */
    public $dataSource = null;

    // data source that stubs records will go in
    // will be clear upon tearDown
    protected $dsAttributes = [
        'key' => 'automated-test',
        'title' => 'Automatically Generated Records',
        'slug' => 'auto-test'
    ];

    public function setUp()
    {
        parent::setUp();

        restore_error_handler();

        $timezone = \ANDS\Util\Config::get('app.timezone');
        date_default_timezone_set($timezone);

        foreach ($this->requiredKeys as $key) {
            $this->ensureKeyExist($key);
        }

        // create the datasource if not exist
        $this->dataSource = DataSourceRepository::getByKey($this->dsAttributes['key']);
        if (!$this->dataSource) {
            $this->dataSource = DataSource::create($this->dsAttributes);
        }
    }

    public function tearDown()
    {
        parent::tearDown();

        if (!$this->dataSource) {
            return;
        }
        // find records that belongs to test data source
        $records = RegistryObject::where('data_source_id', $this->dataSource->id);

        if ($records->count() > 0) {

            $ids = $records->pluck('registry_object_id')->toArray();
            $keys = $records->pluck('key')->toArray();

            // delete all record data
            \ANDS\RecordData::whereIn('registry_object_id', $ids)->delete();

            // delete all relationships
            RegistryObject\Relationship::whereIn('registry_object_id', $ids)->delete();
            RegistryObject\Relationship::whereIn('related_object_key', $keys)->delete();

            // delete all identifiers
            RegistryObject\Identifier::whereIn('registry_object_id', $ids)->delete();

            // delete all identifier relationships
            RegistryObject\IdentifierRelationship::whereIn('registry_object_id', $ids)->delete();

            // delete all records
            $records->delete();
        }

        // delete data source attributes
        $this->dataSource->dataSourceAttributes()->delete();

        // delete data source
        $this->dataSource->delete();
    }

    /**
     * TODO Refactor to Test Factory class
     *
     * @param $class
     * @param array $attributes
     * @param int $count
     * @return mixed
     * @throws Exception
     */
    public function stub($class, $attributes = [], $count = 1)
    {
        if ($class == RegistryObject::class) {
            $title = uniqid();
            $attrs = array_merge([
                'key' => uniqid(),
                'title' => $title,
                'status' => 'PUBLISHED',
                'class' => 'collection',
                'type' => 'dataset',
                'slug' => str_slug($title),
                'data_source_id' => $this->dataSource->id
            ], $attributes);
            $record = RegistryObject::create($attrs);
            return $record;
        } else if ($class == \ANDS\RecordData::class) {
            $attrs = array_merge([
                'registry_object_id' => $this->stub(RegistryObject::class)->id,
                'current' => TRUE,
                'data' => uniqid(),
                'timestamp' => time()
            ], $attributes);
            return \ANDS\RecordData::create($attrs);
        } elseif ($class == RegistryObject\Relationship::class) {
            $attrs = array_merge([
                'registry_object_id' => $this->stub(RegistryObject::class)->id,
                'related_object_key' => $this->stub(RegistryObject::class)->key,
                'origin' => 'EXPLICIT',
                'relation_type' => 'hasAssociationWith'
            ], $attributes);
            return RegistryObject\Relationship::create($attrs);
        } elseif ($class == RegistryObject\IdentifierRelationship::class) {
            $attrs = array_merge([
                'registry_object_id' => $this->stub(RegistryObject::class)->id,
                'related_object_identifier' => 'testID',
                'related_info_type' => 'publication',
                'related_object_identifier_type' => 'handle',
                'relation_type' => 'hasAssociationWith'
            ], $attributes);
            return RegistryObject\IdentifierRelationship::create($attrs);
        } elseif ($class == RegistryObject\Identifier::class) {
            $attrs = array_merge([
                'registry_object_id' => $this->stub(RegistryObject::class)->id,
                'identifier' => uniqid(),
                'identifier_type' => 'test'
            ], $attributes);
            return RegistryObject\Identifier::create($attrs);
        }

        throw new Exception("unknown $class");
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