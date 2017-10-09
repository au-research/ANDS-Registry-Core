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
        $this->assertArraySubset([
            '21' => [
                'type' => 'local',
                'value' => 'localSubject',
                'uri' => ''
            ]
        ], $subjects);
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
    public function it_should_not_get_the_local_notation_mismatch_value_string_subjects(
    )
    {
        $actual = SubjectProvider::resolveSubjects([
            ['type' => 'local', 'value' => '1301 Medical Virology'],
        ]);
        // print_r($resolved_subjects);
        $this->assertArraySubset([
            '1301 Medical Virology' =>
                [
                    'type' => 'local',
                    'value' => '1301 Medical Virology',
                    'resolved' => '1301 Medical Virology',
                    'uri' => ' '
                ]
        ], $actual);
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

        $string = "EARTH SCIENCE > ATMOSPHERE > ATMOSPHERIC RADIATION > ATMOSPHERIC EMITTED RADIATION";
        $this->assertTrue(SubjectProvider::isMultiValue($string));

        $string = "EARTH SCIENCE &gt; ATMOSPHERE &gt; ATMOSPHERIC RADIATION &gt; ATMOSPHERIC EMITTED RADIATION";
        $this->assertTrue(SubjectProvider::isMultiValue($string));
    }

    /** @test **/
    public function it_should_return_narrowest_concept()
    {
        $multi_value = "Earth Science | Atmosphere | Aerosols | Aerosol Backscatter";
        $result = SubjectProvider::getNarrowestConcept($multi_value);
        $this->assertEquals($result,"Aerosol Backscatter");
    }

    /** @test **/
    public function it_should_resolve_iso639_3()
    {
        /*
         * Expected subject values: aag = Ambrak language aah = Arapesh, Abu' aai = Arifama-Miniafia aau = Abau language aaw = Solong abk = Abkhaz language abm = Abanyom language abs = Malay, Ambonese abt = Abelam language abz = Abui language
         */
        $subjects = SubjectProvider::resolveSubjects([
            ['type' => 'iso639-3', 'value' => 'aag'],
            ['type' => 'iso639-3', 'value' => 'aah'],
            ['type' => 'iso639-3', 'value' => 'aai'],
            ['type' => 'iso639-3', 'value' => 'aau'],
            ['type' => 'iso639-3', 'value' => 'aaw'],
            ['type' => 'iso639-3', 'value' => 'abk'],
            ['type' => 'iso639-3', 'value' => 'abm'],
            ['type' => 'iso639-3', 'value' => 'abs'],
            ['type' => 'iso639-3', 'value' => 'abz']
        ]);
        $this->assertEquals($subjects['aag']['resolved'], 'Ambrak language');
        $this->assertEquals($subjects['aah']['resolved'], "Arapesh, Abu'");
        $this->assertEquals($subjects['aai']['resolved'], "Arifama-Miniafia");
        $this->assertEquals($subjects['aau']['resolved'], "Abau language");
        $this->assertEquals($subjects['aaw']['resolved'], "Solong");
        $this->assertEquals($subjects['abk']['resolved'], "Abkhaz language");
        $this->assertEquals($subjects['abm']['resolved'], "Abanyom language");
        $this->assertEquals($subjects['abs']['resolved'], "Malay, Ambonese");
        $this->assertEquals($subjects['abz']['resolved'], "Abui language");

        // TODO: zlm zmc zsm zsu zul zxx
        // TODO: type=local zlm zmc xps
    }

    /** @test **/
    public function it_should_resolve_anzsrc_for()
    {
        // notation only
        $subjects = SubjectProvider::resolveSubjects([
            ['type' => 'anzsrc', 'value' => '01'],
        ]);
        $this->assertEquals($subjects['01']['resolved'], 'MATHEMATICAL SCIENCES');

        // notation with type
        $subjects = SubjectProvider::resolveSubjects([
            ['type' => 'anzsrc-for', 'value' => '01'],
        ]);
        $this->assertEquals($subjects['01']['resolved'], 'MATHEMATICAL SCIENCES');

        // parent
        $subjects = SubjectProvider::resolveSubjects([
            ['type' => 'anzsrc-for', 'value' => '040201'],
        ]);
        $this->assertArrayHasKey('04', $subjects);
        $this->assertArrayHasKey('0402', $subjects);
        $this->assertArrayHasKey('040201', $subjects);

        // sanity check for mixed anzsrc values
        $subjects = SubjectProvider::resolveSubjects([
            ['type' => 'anzsrc-for', 'value' => '05'],
            ['type' => 'anzsrc-for', 'value' => '010201'],
            ['type' => 'anzsrc-for', 'value' => '010206'],
            ['type' => 'anzsrc-for', 'value' => '11 0000'],
        ]);
        $this->assertEquals($subjects['05']['resolved'], "ENVIRONMENTAL SCIENCES");
        $this->assertEquals($subjects['010201']['resolved'], "Approximation Theory and Asymptotic Methods");
        $this->assertEquals($subjects['010206']['resolved'], "Operations Research");
        $this->assertEquals($subjects['0102']['resolved'], "APPLIED MATHEMATICS");
        $this->assertEquals($subjects['01']['resolved'], "MATHEMATICAL SCIENCES");
        $this->assertEquals($subjects['11 0000']['resolved'], "11 0000");

        // TODO: value is both notation and label 0806 Information Services
        // https://test.ands.org.au/registry/registry_object/view/1304
    }

    /** @test **/
    public function it_should_resolve_gcmd_delimited_typed()
    {
        // delimited with > and &gt
        $subjects = SubjectProvider::resolveSubjects([
            ['type' => 'gcmd', 'value' => 'EARTH SCIENCE > ATMOSPHERE > ATMOSPHERIC RADIATION > ATMOSPHERIC EMITTED RADIATION'],
            ['type' => 'gcmd', 'value' => 'Biosphere > Ecological Dynamics > Fire Ecology'],
            ['type' => 'gcmd', 'value' => 'CAML > CENSUS OF ANTARCTIC MARINE LIFE'],
            ['type' => 'gcmd', 'value' => 'CONTINENT > ANTARCTICA > Brattstrand Bluff'],
            ['type' => 'gcmd', 'value' => 'EARTH SCIENCE > ATMOSPHERE > AEROSOLS > EMISSIONS']
        ]);
        $this->assertArrayHasKey("EARTH SCIENCE", $subjects);
        $this->assertArrayHasKey("ATMOSPHERE", $subjects);
        $this->assertArrayHasKey("ATMOSPHERIC RADIATION", $subjects);
        $this->assertArrayHasKey("ATMOSPHERIC EMITTED RADIATION", $subjects);
        $this->assertArrayHasKey("BIOSPHERE", $subjects);
        $this->assertArrayHasKey("ECOLOGICAL DYNAMICS", $subjects);
        $this->assertArrayHasKey("Fire Ecology", $subjects);
        $this->assertArrayHasKey("CAML > CENSUS OF ANTARCTIC MARINE LIFE", $subjects);
        $this->assertArrayHasKey("CONTINENT > ANTARCTICA > Brattstrand Bluff", $subjects);

        // delimited with |
        $subjects = SubjectProvider::resolveSubjects([
            ['type' => 'gcmd', 'value' => 'Earth Science | Land Surface | Land Temperature | Skin Temperature'],
            ['type' => 'gcmd', 'value' => 'Earth Science | Human Dimensions | Habitat Conversion/Fragmentation | Desertification'],
            ['type' => 'gcmd', 'value' => 'Ocean Chemistry | Carbon'],
            ['type' => 'gcmd', 'value' => 'Oceans | Salty'],
            ['type' => 'gcmd', 'value' => 'Earth Science | Climate Indicators | Land Records | Isotopes']
        ]);

        // TODO delimited with | type local

        $this->assertArrayHasKey('EARTH SCIENCE', $subjects);
        $this->assertArrayHasKey('HUMAN DIMENSIONS', $subjects);
        $this->assertArrayHasKey('HABITAT CONVERSION/FRAGMENTATION', $subjects);
        $this->assertArrayHasKey('Desertification', $subjects);
        $this->assertArrayHasKey('OCEANS', $subjects);
        $this->assertArrayHasKey('OCEAN CHEMISTRY', $subjects);
        $this->assertArrayHasKey('Isotopes', $subjects);

        // not resolvable
        $this->assertArrayHasKey('Oceans | Salty', $subjects);
    }

}
