<?php

namespace ANDS\Test;

/**
 * Class TestRegistryObjectIdentifiersExtension
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * @package ANDS\Test
 */
class TestRegistryObjectIdentifiersExtension extends UnitTest
{
    /**
     * @name Test Processing Identifiers
     * @note Make sure that after processing, identifiers are in the Database
     */
    public function testProcessIdentifiers()
    {
        $this->ro->processIdentifiers();
        $result = $this->ci->db->get_where('registry_object_identifiers', ['registry_object_id'=>$this->ro->id]);
        $resultArray = $result->result_array();
        $this->assertTrue($result);
        $this->assertTrue(is_array($resultArray));
        $this->assertEquals(sizeof($resultArray), 2);
    }

    /**
     * @name Test getting the identifiers
     */
    public function testGetIdentifiers()
    {
//        $identifiers = $this->ro->getIdentifiers();
//        $this->assertTrue(is_array($identifiers));
//        $this->assertEquals(sizeof($identifiers), 2);
//        $this->assertContains(["identifier"=>"nla.AUTCollection1", "identifier_type"=>"AU-ANL:PEAU"], $identifiers);
    }

    /**
     * @name Test finding matching records
     */
    public function testFindMatchingRecords()
    {
        $matchingRecords = $this->ro->findMatchingRecords();
        $this->assertGreaterThanOrEqual(sizeof($matchingRecords), 3);
    }

    /**
     * @throws \Exception
     * @todo find a good registry object to test on, this test is now skipped
     */
    public function setUp()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ro = $this->ci->ro->getPublishedBykey("Casdfsdf34");
        if (!$this->ro) {
            throw new \Exception("Record Casdfsdf34 does not exist. Various test cases will be skipped");
        }
    }
}