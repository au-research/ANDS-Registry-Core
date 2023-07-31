<?php

namespace ANDS\Test;

/**
 * Class TestRegistryObjectExtension
 *
 * @package ANDS\Test
 * @author  : Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
class TestRegistryObjectBaseExtension extends UnitTest
{
    // The test record
    private $ro;

    /**
     * @name Testing general get extension via magic method
     */
    public function testBaseExtension()
    {
//        $this->assertEquals("Collection with all RIF v1.6 elements (primaryName)", $this->ro->title);
//        $this->assertEquals("AUTCollection1", $this->ro->key);
//        $this->assertEquals("collection", $this->ro->class);
//        $this->assertEquals("collection", $this->ro->type);
//        $this->assertEquals("AUTestingRecords", $this->ro->group);
    }

    /**
     * @name test the save functionality
     * @note Make sure that after saving, correct fields are saved in the Database
     * @todo
     */
    public function testSave()
    {

    }

    /**
     * @name test creation of new registry object
     * @note Make sure after creating, record exists and can be obtained
     * @todo
     */
    public function testCreate()
    {

    }

    /**
     * @name test removal of a registry object
     * @note creating a new record, then delete them
     * @todo
     */
    public function testDelete()
    {

    }

    public function setUp()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ro = $this->ci->ro->getPublishedByKey("Casdfsdf34");
        if (!$this->ro) {
            throw new \Exception("Record Casdfsdf34 does not exist. Various test cases will be skipped");
        }
    }
}