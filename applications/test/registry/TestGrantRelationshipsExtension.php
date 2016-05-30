<?php

namespace ANDS\Test;

/**
 * Class TestRegistryObjectIdentifiersExtension
 *
 * @author: Liz Woods <liz.woods@ands.org.au>
 * @package ANDS\Test
 */
class TestGrantRelationshipsExtension extends UnitTest
{
    /**
     * @name Test Grants Relationships
     * @note Make sure that after processing, collection relationships generated from grant network have a relationship of funders and an origin of REVERSE_GRANTS
     */
    public function testRelationshipOrigin()
    {
        try {
            $index = $this->ro->getRelationshipIndex();

            $this->assertTrue(is_array($index));
            $this->assertEquals(sizeof($index), 101);
            $this->assertContains(["from_id"=>625278,"from_key"=>"http://dx.doi.org/10.13039/501100003531","from_status"=>"PUBLISHED","from_title"=>"Department of the Environment",
                "from_class"=>"party","from_type"=>"group","from_slug"=>"department-environment","relation_notes"=>"","to_id"=>"2528",
                "to_key"=>"www.ballarat.edu.au/coll/15","to_class"=>"collection","to_type"=>"dataset","to_title"=>"Yarra Ranges ICT survey, 2011","to_slug"=>"yarra-ranges-ict-survey-2011",
            "relation"=>[0=>"isOutputOf",1=>"isFundedBy"],"relation_description"=>[0=>"",1=>""],"relation_url"=>[],"relation_origin"=>[0=>"IDENTIFIER REVERSE",1=>"REVERSE_GRANTS"],"id"=>"6d5eeceb9b804ffbcf0b97726b3e7ac5"], $index);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }


    public function setUp()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ro = $this->ci->ro->getByID(625278);
        if (!$this->ro) {
            throw new \Exception("Record 625278 does not exist. Various test cases will be skipped");
        }
    }
}