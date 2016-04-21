<?php

/**
 * Class:  TestRegistryObjectModel
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\Test;

class TestRegistryObjectModel extends UnitTest
{
    /**
     * @name Test getting a registry object by ID
     * @note result must be of instance _registry_object
     */
    public function testGetRegistryObjectByID()
    {
        $ro = $this->ci->ro->getByID(319959);
        $this->assertInstanceOf($ro, new \_registry_object());
    }

    /**
     * @name Test getting a registry object by Key
     * @note result must be of instance _registry_object
     */
    public function testGetRegistryObjectByKey()
    {
        $ro = $this->ci->ro->getPublishedByKey("AODN:metadata@aad.gov.au");
        $this->assertInstanceOf($ro, new \_registry_object());
    }

    /**
     * @name Test getting a registry object by Slug
     * @note result must be of instance _registry_object
     */
    public function testGetRegistryObjectBySlug()
    {
        $ro = $this->ci->ro->getBySlug("aadc-officer");
        $this->assertInstanceOf($ro, new \_registry_object());
    }

    /**
     * @throws \Exception
     * @name param 319959 must have related objects
     * @note relatedObjects must be a non empty array
     */
    public function testGetRelatedObjects()
    {
        $ro = $this->ci->ro->getByID(319959);
        if ($ro) {
            $relatedObjects = $ro->getAllRelatedObjects();
            $this->assertTrue(is_array($relatedObjects));
            $this->assertGreaterThan(sizeof($relatedObjects), 0);
        } else {
            throw new \Exception("Record 319959 does not exist");
        }
    }

    public function setUp()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
    }

    public function tearDown()
    {

    }


}