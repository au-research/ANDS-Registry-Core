<?php

namespace ANDS\Test;

/**
 * Class TestGroupCoreAttribute
 *
 * @package ANDS\Test
 * @author: Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 */
use _registry_object as _registry_object;

class TestGroupCoreAttribute extends UnitTest
{
    /**
     * @name new Object has a group as a core attribute
     */
    public function test_that_new_object_has_group_as_core_attribute()
    {
        $ro = new _registry_object();
        $ro->group = "Test Group";
        $groupAttribute = $ro->attributes['group'];
        $this->assertTrue($groupAttribute->core === TRUE);
    }

    /**
     * @name test that getting existing group is core
     */
    public function test_that_existing_object_has_group_as_core_attribute()
    {
        $ro = $this->ci->ro->getPublishedByKey('AUTestingRecords3/grants/AUT/Hub2');
        $groupAttribute = $ro->attributes['group'];
        $this->assertTrue($groupAttribute->core === TRUE);
    }

    public function test_if_edit_an_existing_object_the_value_get_stored_correctly()
    {
        $ro = $this->ci->ro->getPublishedByKey('AUTestingRecords3/grants/AUT/Hub2');

        $oldGroup = $ro->group;

        $newGroup = $oldGroup . "-MODIFIED";
        $ro->group = $newGroup;
        $ro->save();

        // test in the database here
        $query = $this->ci->db->get_where('registry_objects', ['key' => 'AUTestingRecords3/grants/AUT/Hub2', 'status' => 'PUBLISHED']);
        $group = $query->first_row()->group;
        $this->assertTrue($group == $newGroup);

        // // return it back
        $ro->group = $oldGroup;
        $ro->save();

        // test it again
        $query = $this->ci->db->get_where('registry_objects', ['key' => 'AUTestingRecords3/grants/AUT/Hub2', 'status' => 'PUBLISHED']);
        $group = $query->first_row()->group;
        $this->assertTrue($group == $oldGroup);
    }

    public function setUp()
    {
        require_once(REGISTRY_APP_PATH. "registry_object/models/_registry_object.php");
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ci->load->model('registry/data_source/data_sources', 'ds');
        $this->ci->load->database();
    }

    public function tearDown()
    {

    }


}