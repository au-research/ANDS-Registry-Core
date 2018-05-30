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

        date_default_timezone_set(\ANDS\Util\Config::get('app.timezone'));

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

        // find records that belongs to test data source
        $records = RegistryObject::where('data_source_id', $this->dataSource->id);

        // delete all record data
        \ANDS\RecordData::whereIn('registry_object_id', $records->pluck('registry_object_id')->toArray())->delete();

        // delete all records
        $records->delete();

        // delete data source
        $this->dataSource->delete();
    }

    /**
     * @param $class
     * @param array $attributes
     * @param int $count
     * @return mixed
     * @throws Exception
     */
    public function stub($class, $attributes = [], $count = 1)
    {
        if ($class == RegistryObject::class) {
            $attrs = array_merge([
                'key' => uniqid(),
                'title' => uniqid(),
                'status' => 'PUBLISHED',
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