<?php


namespace ANDS\Test;

use ANDS\Registry\Connections;

use ANDS\Repository\CIActiveRecordConnectionsRepository as Repository;

class TestConnections extends UnitTest
{

    /**
     * @name basic test
     */
    public function test_basic_get()
    {
        $conn = new Connections(new Repository($this->ci->db));
        $explicitLinks = $conn->get();
        $this->assertTrue(sizeof($explicitLinks) > 0);
    }

    /**
     * @name test with limit and offset
     */
    public function test_basic_get_with_limit_and_offset()
    {
        $repository = new Repository($this->ci->db);
        $conn = new Connections($repository);
        $explicitLinks = $conn
            ->setLimit(30)
            ->setOffset(0)
            ->get();
        $this->assertTrue(sizeof($explicitLinks) === 30);
    }

    /**
     * @name get explicit by key
     */
    public function test_getExplicitRelationByKey()
    {
        $conn = new Connections(new Repository($this->ci->db));
        $links = $conn->getExplicitRelationByKey('http://AUT.org/au-research/grants/arc/DP0987282');
        $this->assertEquals(2, sizeof($links));
    }

    /**
     * @name test get reverse relation by key
     */
    public function test_getReverseRelationByKey()
    {
        $conn = new Connections(new Repository($this->ci->db));
        $links = $conn->getReverseRelationByKey('http://anu.edu.au/anudc:3316');
        $this->assertTrue(sizeof($links) >= 1);
    }

    /**
     * @name test get some flag and from key filter
     */
    public function test_get_with_some_filter()
    {
        $repository = new Repository($this->ci->db);
        $conn = new Connections($repository);
        $explicitLinks = $conn
            ->setFlag(['from_key', 'from_group', 'to_key'])
            ->setFilter('from_key', 'http://AUT.org/au-research/grants/arc/DP0987282')
            ->get();

        $this->assertEquals(2, sizeof($explicitLinks));

        // @todo make sure content is correct
    }

    /**
     * @name test get relation type
     */
    public function test_get_relation_type()
    {
        $repository = new Repository($this->ci->db);
        $conn = new Connections($repository);
        $explicitLinks = $conn
            ->setFlag(['from_key', 'from_group', 'to_key'])
            ->setFilter('relation_type', 'isPartof')
            ->setFilter('from_key', 'http://AUT.org/au-research/grants/arc/DP0987282')
            ->get();

        $this->assertEquals(1, sizeof($explicitLinks));
    }

    public function test_getAODN()
    {
        $conn = new Connections(new Repository($this->ci->db));
        $links = $conn
            ->setFilter('to_key', 'AODN:metadata@aad.gov.au')
            ->setFilter('from_status', 'PUBLISHED')
            ->setFilter('to_status', 'PUBLISHED')
            ->setFilter('from_class', 'collection')
            ->setLimit(99999)
            ->get();

        $this->assertEquals(1257, count($links));
    }
    

    // 'eAtlas/AustralianInstituteofMarineScience(AIMS)' to key

    public function test_stringFilterWithLimit()
    {
        $conn = new Connections(new Repository($this->ci->db));
        $conn
            ->setLimit(10)
            ->setFilter('from_data_source_id != to_data_source_id');
        $links = $conn->get();

        foreach ($links as $link) {
            $prop = $link->getProperties();
            $this->assertTrue($prop['from_data_source_id'] != $prop['to_data_source_id']);
        }
    }

    /**
     * @name test get multiple relation type
     */
    public function test_get_relation_type_multi()
    {
        $repository = new Repository($this->ci->db);
        $conn = new Connections($repository);
        $explicitLinks = $conn
            ->setFlag(['from_key', 'from_group', 'to_key'])
            ->setFilter('relation_type', ['isPartof', 'isManagedBy'])
            ->setFilter('from_key', 'http://AUT.org/au-research/grants/arc/DP0987282')
            ->get();

        $this->assertEquals(2, sizeof($explicitLinks));
    }
}