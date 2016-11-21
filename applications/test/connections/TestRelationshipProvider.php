<?php


namespace ANDS\Test;

use ANDS\Registry\Providers\RelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Registry\Providers\GrantsConnectionsProvider;

/**
 * Class TestRelationshipProvider
 * @package ANDS\Test
 */
class TestRelationshipProvider extends UnitTest
{
    /** @test **/
    public function test_it_should_find_and_save_the_funder()
    {
        $activityKey = 'http://purl.org/au-research/grants/arc/DP0664065';
        $record = RegistryObjectsRepository::getPublishedByKey($activityKey);

        // process
        RelationshipProvider::processGrantsRelationship($record);

        // make sure it's in the database
        $metadata = $record->getRegistryObjectMetadata('funder_id');
        $this->assertTrue($metadata);

        // get it
        $grantsRelationship =  RelationshipProvider::getGrantsRelationship($record);
        $funder = $grantsRelationship['funder'];

        // make sure it's the same as the generated ones
        $generatedFunder = GrantsConnectionsProvider::create()->getDirectFunder($record);

        $this->assertEquals($funder, $generatedFunder);
    }
}