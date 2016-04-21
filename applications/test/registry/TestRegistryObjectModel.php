<?php

/**
 * Class:  TestRegistryObjectModel
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\Test;


class TestRegistryObjectModel extends UnitTest
{

    private $testRecord;

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

    public function setUp()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
    }

    public function tearDown() {

    }


}