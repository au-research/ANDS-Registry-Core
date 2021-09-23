<?php


namespace ANDS\Test;

use ANDS\Repository\CIActiveRecordConnectionsRepository as Repository;

/**
 * Class TestCIActiveRecordConnectionsRepository
 * @package ANDS\Test
 */
class TestCIActiveRecordConnectionsRepository extends UnitTest
{
    private $flags = [
        'from_id',
        'from_key',
        'from_title',
        'from_slug',
        'from_class',
        'from_type',
        'from_data_source_id',
        'relation_type',
        'relation_description',
        'relation_url',
        'relation_origin',
        'to_id',
        'to_key',
        'to_title',
        'to_slug',
        'to_class',
        'to_type',
        'to_data_source_id'
    ];

    /** @test **/
    public function test_it_should_be_able_to_get_by_filter()
    {
        $repo = new Repository;
        $records = $repo->run([
            'to_key' => 'research-data.ansto.gov.au/collection/771'
        ], $this->flags);
        $this->assertGreaterThan(count($records), 0);
    }

    /** @test **/
    public function test_it_should_be_able_to_get_by_multiple_filter()
    {
        $repo = new Repository;
        $records = $repo->run([
            'to_key' => 'research-data.ansto.gov.au/collection/771',
            'to_class' => 'collection',
            'to_type' => 'repository'
        ], $this->flags);
        $this->assertGreaterThan(count($records), 0);
    }

    /** @test **/
    public function test_it_should_be_able_to_limit_and_offset()
    {
        // get the first record
        $repo = new Repository;
        $records = $repo->run([
            'to_key' => 'research-data.ansto.gov.au/collection/771',
            'to_class' => 'collection',
            'to_type' => 'repository'
        ], $this->flags, 1, 0);
        $this->assertEquals(count($records), 1);
        $firstRecord = $records[0];

        // get the second record
        $records = $repo->run([
            'to_key' => 'research-data.ansto.gov.au/collection/771',
            'to_class' => 'collection',
            'to_type' => 'repository'
        ], $this->flags, 1, 1);
        $secondRecord = $records[0];

        // first is different from second
        $this->assertNotEquals($firstRecord['from_id'], $secondRecord['from_id']);
    }

    /** @test **/
    public function test_it_should_be_able_to_where_in()
    {
        $repo = new Repository;
        $records = $repo->run([
            'from_key' => [
                'research-data.ansto.gov.au/collection/771',
                'http://www.rmit.edu.au/HPC/2/33'
            ],
        ]);

        // there should be 2 from_keys in the result
        $fromKeys = collect($records)->pluck('from_key')->unique()->toArray();
        $this->assertEquals(count($fromKeys), 2);
    }
}