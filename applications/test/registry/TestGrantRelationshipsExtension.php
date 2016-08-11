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

            // a particular relation must have REVERSE_GRANTS on it
            $relation = array_filter($index, function($item) {
               return
                    $item['from_key'] == "http://dx.doi.org/10.13039/501100003531"
                    && $item['to_key'] == "www.ballarat.edu.au/coll/15";
            });
            $this->assertEquals(count($relation), 1);
            $relation = array_values($relation);
            $relation = array_pop($relation);
            $this->assertContains("REVERSE_GRANTS", $relation['relation_origin']);


        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }


    public function setUp()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');
        $this->ro = $this->ci->ro->getPublishedByKey("http://dx.doi.org/10.13039/501100003531");
        if (!$this->ro) {
            throw new \Exception("Record http://dx.doi.org/10.13039/501100003531 does not exist. Various test cases will be skipped");
        }
    }
}