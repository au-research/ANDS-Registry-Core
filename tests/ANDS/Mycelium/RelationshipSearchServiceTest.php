<?php
namespace ANDS\Mycelium;

use ANDS\DataSource;
use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Backup\BackupRepository;
use ANDS\Registry\Importer;
use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;

class RelationshipSearchServiceTest extends \MyceliumTestClass
{


    /**
     * @test
     */
    public function test_until_we_get_solr_8_in_ci_machine()
    {
        $this->assertTrue(true);
    }

    /** @test
     *
     * public function moved_to_integration_test_it_should_not_return_related_reverse()
     * {
     * BackupRepository::restore("16_RelationshipScenario", $options = [
     * 'includeGraphs' => true,
     * 'includePortalIndex' => false,
     * 'includeRelationshipsIndex' => true
     * ]);
     *
     * $dataSource1 = DataSourceRepository::getByKey("16_RelationshipScenario_AUTestingRecords");
     * $dataSource2 = DataSourceRepository::getByKey("16_RelationshipScenario_AUTestingRecords2");
     * $dataSource3 = DataSourceRepository::getByKey("16_RelationshipScenario_AUTestingRecords3");
     *
     * //This record has 5 internal reverse relationships
     * $this->ensureKeyExist("P5_16");
     * $record = RegistryObjectsRepository::getPublishedByKey("P5_16");
     * $relatedRecords = RelationshipProvider::get($record);
     * $this->assertEquals(5, sizeof($relatedRecords));
     *
     * //turn off allow_reverse_internal_links and check relationships aren't returned
     * $dataSource3->setDataSourceAttribute('allow_reverse_internal_links',null);
     * $relatedRecords = RelationshipProvider::get($record);
     * $this->assertEquals(0, sizeof($relatedRecords));
     *
     * $dataSource3->setDataSourceAttribute('allow_reverse_internal_links',1);
     *
     *
     * //This record has an external reverse relationships
     * $this->ensureKeyExist("C4_16");
     * $record = RegistryObjectsRepository::getPublishedByKey("C4_16");
     * $relatedRecords = RelationshipProvider::get($record);
     * $this->assertEquals(6, sizeof($relatedRecords));
     *
     * $dataSource2->setDataSourceAttribute('allow_reverse_external_links',null);
     * $relatedRecords = RelationshipProvider::get($record);
     *
     * $this->assertEquals(2, sizeof($relatedRecords));
     *
     * Importer::wipeDataSourceRecords($dataSource1, $softDelete = false);
     *
     * Importer::wipeDataSourceRecords($dataSource2, $softDelete = false);
     *
     * Importer::wipeDataSourceRecords($dataSource3, $softDelete = false);
     * }
     * }
     */
}
