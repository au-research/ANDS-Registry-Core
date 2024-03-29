<?php

namespace ANDS\Registry\Suggestors;


class SubjectSuggestorTest extends \RegistryTestClass
{

    /** @test */
    public function test_basic_suggestor_passing()
    {
        $record = $this->ensureKeyExist("AODN/8f520751-7483-435a-b701-c6b1ec6f3a2b");
        $suggestor = new SubjectSuggestor();
        $suggestions = $suggestor->suggest($record);
       // $this->assertNotEmpty($suggestions);
    }

    /** @test */
    public function test_getSuggestorQuery()
    {
        // it should search for subject_value_resolved_search with all the subjects field
        $suggestor = new SubjectSuggestor();

        // use the right field
        $this->assertEquals(
            $suggestor->getSuggestorQuery([
                'EARTH SCIENCE',
                'SCIENCE'
            ]),
            "(subject_value_unresolved:\"EARTH SCIENCE\") OR (subject_value_unresolved:\"SCIENCE\")"
        );

        // escape solr values properly
        $this->assertEquals(
            $suggestor->getSuggestorQuery([
                'EARTH SCIENCE',
                'EARTH SCIENCE | BIOLOGICAL CLASSIFICATION | PLANTS | ANGIOSPERMS (FLOWERING PLANTS) | MONOCOTS | SEAGRASS'
            ]),
            "(subject_value_unresolved:\"EARTH SCIENCE\") OR (subject_value_unresolved:\"EARTH SCIENCE \| BIOLOGICAL CLASSIFICATION \| PLANTS \| ANGIOSPERMS \(FLOWERING PLANTS\) \| MONOCOTS \| SEAGRASS\")"
        );
    }
}
