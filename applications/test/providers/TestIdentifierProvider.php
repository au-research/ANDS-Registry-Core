<?php


namespace ANDS\Test;

use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\RegistryObject;
use ANDS\RegistryObject\Identifier;
use ANDS\Repository\RegistryObjectsRepository;

class TestIdentifierProvider extends UnitTest
{
    /**
     * Class TestRelationshipProvider
     * @package ANDS\Test
     */

    /** @test * */
    public function test_it_sould_delete_all_Identifiers()
    {
        // $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';
        $record = $this->ensureKeyExist('AUTCollection2');
        $Identifiers = Identifier::where('registry_object_id',
            $record->registry_object_id)->get();

        IdentifierProvider::deleteAllIdentifiers($record);

        $shouldBeEmptyIdentifiers = Identifier::where('registry_object_id',
            $record->registry_object_id)->get();

        $this->assertEquals(count($shouldBeEmptyIdentifiers), 0);

        //QualityMetadataProvider::process($record);
    }

    public function test_it_sould_create_Identifiers()
    {
        // $collectionkey = 'AUTestingRecords3RelatedCollectionDatasetRelObj1';

        $record = $this->ensureKeyExist('AUTCollection2');

        IdentifierProvider::process($record);

        $Identifiers = Identifier::where('registry_object_id',
            $record->registry_object_id)->get();
        $this->assertEquals(count($Identifiers), 31);

    }

}