<?php


namespace ANDS\Providers\RIFCS;


use ANDS\Registry\Providers\RIFCS\SubjectProvider;
use ANDS\Repository\RegistryObjectsRepository;

class SubjectsProviderTest extends \RegistryTestClass
{
    protected $requiredKeys = [
//        "AUTCollectionToTestSearchFields37",
//        "AODN/073fde5a-bff3-1c1f-e053-08114f8c5588",
//        "1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn"
    ];

    /** @test **/
    public function it_should_get_the_subjects()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $subjects = SubjectProvider::getSubjects($record);
        $this->assertArraySubset(['0' => ['type' => 'local','value' => 'localSubject','uri' => '']], $subjects);
    }

    /** @test **/
    public function it_should_get_the_resolved_subjects()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AUTCollectionToTestSearchFields37");
        $resolved_subjects = SubjectProvider::processSubjects($record);
      //  print_r($resolved_subjects);
        $this->assertArraySubset(['03'=>
            ['type' => 'anzsrc-for',
            'value' => '03',
            'resolved' => 'CHEMICAL SCIENCES',
            'uri' => 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/03']], $resolved_subjects);
    }
    /** @test **/
    public function it_should_get_the_non_notation_subjects()
    {
        $record = RegistryObjectsRepository::getPublishedByKey("AODN/073fde5a-bff3-1c1f-e053-08114f8c5588");
        $resolved_subjects = SubjectProvider::processSubjects($record);
       // dd($resolved_subjects);
        $this->assertArraySubset(['Earth Science | Atmosphere | Atmospheric Radiation | Solar Radiation'=>
            ['type' => 'gcmd',
                'value' => 'Earth Science | Atmosphere | Atmospheric Radiation | Solar Radiation',
                'resolved' => 'SOLAR RADIATION',
                'uri' => 'http://gcmdservices.gsfc.nasa.gov/kms/concept/a0f3474e-9a54-4a82-97c4-43864b48df4c']], $resolved_subjects);
    }

    /** @test **/
    public function it_should_get_the_notation_value_string_subjects()
    {
        $this->ensureKeyExist("1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn");
        $record = RegistryObjectsRepository::getPublishedByKey("1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn");
        $resolved_subjects = SubjectProvider::processSubjects($record);
        $this->assertArraySubset(['1108' =>
           ['type' => 'anzsrc-for',
            'value' => '1108',
            'resolved' => 'MEDICAL MICROBIOLOGY',
            'uri' => 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/1108']], $resolved_subjects);
    }

    /** @test **/
    public function it_should_not_get_the_local_notation_mismatch_value_string_subjects()
    {
        $this->ensureKeyExist("1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn");
        $record = RegistryObjectsRepository::getPublishedByKey("1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn");
        $resolved_subjects = SubjectProvider::processSubjects($record);
       // print_r($resolved_subjects);
        $this->assertArraySubset(['1301 Medical Virology' =>
            ['type' => 'local',
                'value' => '1301 Medical Virology',
                'resolved' => '1301 Medical Virology',
                'uri' => '']], $resolved_subjects);
    }

    /** @test **/
    public function it_should_resolve_the_iso639_notation()
    {
        $this->ensureKeyExist("1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn");
        $record = RegistryObjectsRepository::getPublishedByKey("1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn");
        $resolved_subjects = SubjectProvider::processSubjects($record);
        // print_r($resolved_subjects);
        $this->assertArraySubset(['tpi' =>
            ['type' => 'iso639-3',
                'value' => 'tpi',
                'resolved' => 'Tok Pisin',
                'uri' => 'http://lexvo.org/id/iso639-3/tpi']], $resolved_subjects);
    }
}
