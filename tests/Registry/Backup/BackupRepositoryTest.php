<?php

namespace Registry\Backup;

use ANDS\DataSource;
use ANDS\File\Storage;
use ANDS\Registry\Backup\BackupRepository;
use ANDS\Registry\Importer;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;
use PHPUnit\Framework\TestCase;

class BackupRepositoryTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        BackupRepository::init([
            'path' => Storage::disk('test')->getPath('backups')
        ]);

        if ($dataSource = DataSource::find(502997)) {
            Importer::wipeDataSourceRecords($dataSource, false);
            DataSource::destroy("502997");
        }
    }

    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub

        if ($dataSource = DataSource::find(502997)) {
            Importer::wipeDataSourceRecords($dataSource, false);
            DataSource::destroy("502997");
        }
    }

    public function test_that_it_throws_exception_when_validate_backup_with_existing_datasource()
    {
        $dataSourceId = "502997";
        if (DataSourceRepository::getByID($dataSourceId)) {
            $this->markTestSkipped("DataSource[id=502997] already exists, this test cannot be ran");
        }

        // DataSource[id="502997"] should exists prior to testing
        DataSource::unguard();
        DataSource::firstOrCreate([
            'data_source_id' => $dataSourceId,
            'title' => uniqid(),
            'key' => uniqid(),
            'slug' => uniqid()
        ]);

        // throws exception when attempt to create data source
        $this->setExpectedException(\Exception::class);
        BackupRepository::validateBackup("valid-backup-1-datasource");
    }

    public function test_that_it_throws_exception_when_validate_backup_with_unknown_backup()
    {
        BackupRepository::validateBackup("UNKNOWN");
    }

    public function test_that_it_restores()
    {
        BackupRepository::restore("valid-backup-1-datasource", [
            "includeGraphs" => false,
            "includePortalIndex" => false,
            "includeRelationshipsIndex" => false
        ]);

        // data source is created
        $this->assertNotNull(DataSourceRepository::getByID(502997));

        // record is created
        $this->assertNotNull(RegistryObjectsRepository::getRecordByID(502997));
    }

    public function test_that_it_restores_and_overwrite()
    {
        // create the data source and record with the conflicting id first
        $dataSourceId = "502997";
        DataSource::unguard();
        DataSource::firstOrCreate([
            'data_source_id' => $dataSourceId,
            'title' => "Existing DataSource",
            'key' => uniqid(),
            'slug' => uniqid()
        ]);

        // record id
        $recordId = "502997";
        RegistryObject::unguard();
        RegistryObject::firstOrCreate([
            'registry_object_id' => $recordId,
            'data_source_id' => $dataSourceId,
            'title' => 'Existing Record'
        ]);

        // when performing the backup
        BackupRepository::restore("valid-backup-1-datasource", [
            "includeGraphs" => false,
            "includePortalIndex" => false,
            "includeRelationshipsIndex" => false
        ]);

        // the data source is overwritten
        $this->assertNotEquals("Existing DataSource", DataSourceRepository::getByID(502997)->title);

        // the record is also overwritten
        $this->assertNotEquals("Existing Record", RegistryObjectsRepository::getRecordByID(502997)->title);
    }
}
