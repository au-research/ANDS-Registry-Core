<?php

namespace ANDS\Test;

/**
 * Class TestRegistryObjectModel
 *
 * @package ANDS\Test
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class TestRegistryObjectModel extends UnitTest
{

    /**
     * @name Test getting a registry object by Key
     * @note result must be of instance _registry_object
     */
    public function testGetRegistryObjectByKey()
    {
        $ro = $this->ci->ro->getPublishedByKey("AUTCollection1");
        $this->assertInstanceOf($ro, new \_registry_object());
    }

    /**
     * @name Test getting a registry object by Slug
     * @note result must be of instance _registry_object
     */
    public function testGetRegistryObjectBySlug()
    {
        $ro = $this->ci->ro->getBySlug("collection-rif-v16-elements-primaryname");
        $this->assertInstanceOf($ro, new \_registry_object());
    }
    
    /**
     * @name param 319959 must have related objects
     * @note relatedObjects must be a non empty array
     */
    public function testGetRelatedObjects()
    {
        $relatedObjects = $this->ro->getAllRelatedObjects();
        $this->assertTrue(is_array($relatedObjects));
        $this->assertGreaterThan(sizeof($relatedObjects), 0);
    }

    public function setUp()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ro = $this->ci->ro->getPublishedByKey("Casdfsdf34");
        if (!$this->ro) {
            throw new \Exception("Record Casdfsdf34 does not exist. Various test cases will be skipped");
        }
    }

    public function tearDown()
    {

    }
    
}