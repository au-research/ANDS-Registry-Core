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
    public function testGetORCID_XMLByKey()
    {
        $ro = $this->ci->ro->getPublishedByKey("10.4225/06/565E7702F1E12");
        $this->assertInstanceOf($ro, new \_registry_object());
        $xml = $ro->transformToORCID();
        echo($xml);
    }


    public function testOrcidImport()
    {
        $ro = $this->ci->ro->getPublishedByKey("10.4225/06/565E7702F1E12");
        $this->assertInstanceOf($ro, new \_registry_object());
        $xml = $ro->transformToORCID();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
<orcid-message
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns="http://www.orcid.org/ns/orcid" 
    xmlns:common="http://www.orcid.org/ns/common" 
    xmlns:work="http://www.orcid.org/ns/work" 
    xsi:schemaLocation="http://www.orcid.org/ns/orcid 
    https://raw.githubusercontent.com/ORCID/ORCID-Source/master/orcid-model/src/main/resources/orcid-message-1.2.xsd 
    http://www.orcid.org/ns/common 
    https://raw.githubusercontent.com/ORCID/ORCID-Source/master/orcid-model/src/main/resources/common_2.0/common-2.0.xsd 
    http://www.orcid.org/ns/work 
    https://raw.githubusercontent.com/ORCID/ORCID-Source/master/orcid-model/src/main/resources/record_2.0/work-2.0.xsd">
<message-version>1.2</message-version>
<orcid-profile>
  <orcid-activities>
    <orcid-works> 
      '.$xml.'
    </orcid-works>
  </orcid-activities>
</orcid-profile>
</orcid-message>';

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