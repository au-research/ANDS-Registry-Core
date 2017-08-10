<?php


namespace ANDS\Providers\RIFCS;


use ANDS\Registry\Providers\RIFCS\SubjectProvider;
use ANDS\Repository\RegistryObjectsRepository;

class SubjectsProviderTest extends \RegistryTestClass
{
    /** @test **/
   public function it_should_get_the_subjects()
    {
        $key = "1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        $subjects = SubjectProvider::getSubjects($record);
        //print_r($subjects);
        $this->assertArraySubset(['21' => ['type' => 'local','value' => 'localSubject','uri' => '']], $subjects);
    } 

    /** @test **/
    public function it_should_get_the_resolved_subjects()
    {
        $key = "1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
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
        $key = "1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
        $resolved_subjects = SubjectProvider::processSubjects($record);
       // dd($resolved_subjects);
        $this->assertArraySubset(['Solar Radiation'=>
            ['type' => 'gcmd',
                'value' => 'Solar Radiation',
                'resolved' => 'SOLAR RADIATION',
                'uri' => 'http://gcmdservices.gsfc.nasa.gov/kms/concept/a0f3474e-9a54-4a82-97c4-43864b48df4c']], $resolved_subjects);
    }

    /** @test **/
    public function it_should_get_the_notation_value_string_subjects()
    {
        $key = "1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);
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
          $key = "1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn";
          $this->ensureKeyExist($key);
          $record = RegistryObjectsRepository::getPublishedByKey($key);
          $resolved_subjects = SubjectProvider::processSubjects($record);
         // print_r($resolved_subjects);
          $this->assertArraySubset(['1301 Medical Virology' =>
              ['type' => 'local',
                  'value' => '1301 Medical Virology',
                  'resolved' => '1301 Medical Virology',
                  'uri' => ' ']], $resolved_subjects);
      }

    /** @test **/
    public function it_should_resolve_the_iso639_notation()
    {
        $key = "1CRE9ad2CNJUaTtV571LDcrGL3E14lIWNqrNrvGT8fE8ZXUVUMwn";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);

        $resolved_subjects = SubjectProvider::processSubjects($record);
       // print_r($resolved_subjects);
        $this->assertArraySubset(['tpi' =>
            ['type' => 'iso639-3',
                'value' => 'tpi',
                'resolved' => 'Tok Pisin',
                'uri' => 'http://lexvo.org/id/iso639-3/tpi']], $resolved_subjects);
    }

    /** @test **/
    public function it_should_return_true_multivalue_string()
    {
        $multi_value = "Earth Science | Atmosphere | Aerosols | Aerosol Backscatter";
        $result = SubjectProvider::isMultiValue($multi_value);
        $this->assertTrue($result);
    }

    /** @test **/
    public function it_should_return_narrowest_concept()
    {
        $multi_value = "Earth Science | Atmosphere | Aerosols | Aerosol Backscatter";
        $result = SubjectProvider::getNarrowestConcept($multi_value);
        $this->assertEquals($result,"Aerosol Backscatter");
    }

    /** @test **/
    public function it_should_process_subjects_correctly()
    {
//        $key = "CEL/IMOS/a681fdba-c6d9-44ab-90b9-113b0ed03536";
//        $key = "IMOS/a681fdba-c6d9-44ab-90b9-113b0ed03536";
//        $key = "IMOS/5f4c0250-20cc-4e9e-b71f-073e848fe9b7";
        $key = "k1lZPQvetu44OSGXCu1z2gOKrduo553fXf5CVqHfLCapkHdN25cU";
//        $key = "bbbbbk1lZPQvetu44OSGXCu1z2gOKrduo553fXf5CVqHfLCapkHdN25cU";
        $this->ensureKeyExist($key);
        $record = RegistryObjectsRepository::getPublishedByKey($key);

        $resolved_subjects = SubjectProvider::processSubjects($record);
//        dd($resolved_subjects);
        $this->assertTrue(is_array($resolved_subjects));
    }
}
