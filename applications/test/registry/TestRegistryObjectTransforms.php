<?php

namespace ANDS\Test;
/**
 * Class TestRegistryObjectModel
 *
 * @package ANDS\Test
 * @author: u4187959
 */
class TestRegistryObjectTransforms extends UnitTest
{

    /**
     * @name Test getting ORCID xml for collection
     * @note B#
     */
    public function testTransformToHtml()
    {
        $ro = $this->ci->ro->getPublishedByKey("10.4225/06/565E7702F1E12");
        $this->assertInstanceOf($ro, new \_registry_object());
        $xml = $ro->transformForHtml();
        echo($xml);
    }



    public function setUp()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ro = $this->ci->ro->getPublishedByKey("10.4225/06/565E7702F1E12");
        if (!$this->ro) {
            throw new \Exception("Record Casdfsdf34 does not exist. Various test cases will be skipped");
        }
    }

    public function tearDown()
    {

    }

}