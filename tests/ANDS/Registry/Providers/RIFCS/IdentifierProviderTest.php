<?php

use ANDS\File\Storage;
use ANDS\RecordData;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;

use ANDS\Registry\Providers\RIFCS\RIFCSIndexProvider;
use ANDS\RegistryObject;
use Illuminate\Database\Capsule\Manager as Capsule;

class IdentifierProviderTest extends \RegistryTestClass
{
    /** @test * */
    public function it_should_process_dois()
    {
        $tests = [
            ['value' => 'DOI:10.234/455', 'type' => 'doi', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            ['value' => 'http://doi.org/10.234/455', 'type' => 'url', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            ['value' => 'https://doi.org/10.234/455', 'type' => 'uri', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            ['value' => 'https://doi.org/10.234/455', 'type' => 'doi', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            ['value' => '10.234/455', 'type' => 'doi', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            // NOT DOIs
            ['value' => '1.234/455', 'type' => 'fish', 'expectedValue' => '1.234/455', 'expectedType' => 'fish'],
            ['value' => 'http://doi.org/1.234/455', 'type' => 'url', 'expectedValue' => 'doi.org/1.234/455', 'expectedType' => 'url']
        ];
        foreach ($tests as $test) {
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

    /** @test * */
    public function it_should_process_orcid()
    {
        $tests = [
            ['value' => 'http://http://orcid.org/0000-0001-7212-0667', 'type' => 'uri', 'expectedValue' => '0000-0001-7212-0667', 'expectedType' => 'orcid'],
            ['value' => 'http://orcid.org/0000-0002-9539-5716', 'type' => 'url', 'expectedValue' => '0000-0002-9539-5716', 'expectedType' => 'orcid'],
            ['value' => 'https://orcid.org/0000-0002-9539-5716', 'type' => 'url', 'expectedValue' => '0000-0002-9539-5716', 'expectedType' => 'orcid'],
            ['value' => 'https://orcid.org/0000-0002-9539-5716/userInfo.csv', 'type' => 'url', 'expectedValue' => '0000-0002-9539-5716', 'expectedType' => 'orcid'],
            ['value' => '0000-0002-9539-5716', 'type' => 'orcid', 'expectedValue' => '0000-0002-9539-5716', 'expectedType' => 'orcid'],
            // NOT ORCIDS
            ['value' => 'http://orcid.org/index.php', 'type' => 'url', 'expectedValue' => 'orcid.org/index.php', 'expectedType' => 'url'],
            ['value' => 'http://forcid.org/9539-5716', 'type' => 'url', 'expectedValue' => 'forcid.org/9539-5716', 'expectedType' => 'url']
        ];
        foreach ($tests as $test) {
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

    /** @test * */
    public function it_should_process_handles()
    {
        $tests = [
            ['value' => 'hdl:1959.7/512474', 'type' => 'handle', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => 'hdl:1959.7/512474', 'type' => 'global', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => 'hdl.handle.net/1959.7/512474', 'type' => 'url', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => 'https://hdl.handle.net/1959.7/512475', 'type' => 'uri', 'expectedValue' => '1959.7/512475', 'expectedType' => 'handle'],
            ['value' => 'http://hdl.handle.net/1959.7/512476', 'type' => 'handle', 'expectedValue' => '1959.7/512476', 'expectedType' => 'handle'],
            ['value' => '1959.7/512474', 'type' => 'handle', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => 'http://researchdata.ands.org.au/view/?key=http://hdl.handle.net/1959.14/201435', 'type' => 'uri', 'expectedValue' => 'researchdata.ands.org.au/view/?key=http://hdl.handle.net/1959.14/201435', 'expectedType' => 'uri']

        ];
        foreach ($tests as $test) {
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

    /** @test * */
    public function it_should_process_purlds()
    {
        $tests = [
            ['value' => 'http://purl.org/au-research/grants/nhmrc/GNT1002592', 'type' => 'uri', 'expectedValue' => 'https://purl.org/au-research/grants/nhmrc/GNT1002592', 'expectedType' => 'purl'],
            ['value' => 'http://purl.org/au-research/grants/nhmrc/GNT1002592', 'type' => 'purl', 'expectedValue' => 'https://purl.org/au-research/grants/nhmrc/GNT1002592', 'expectedType' => 'purl'],
            ['value' => 'https://purl.org/au-research/grants/nhmrc/GNT1002592', 'type' => 'global', 'expectedValue' => 'https://purl.org/au-research/grants/nhmrc/GNT1002592', 'expectedType' => 'purl'],
            ['value' => 'https://purl.org/au-research/grants/nhmrc/GNT1002592', 'type' => 'url', 'expectedValue' => 'https://purl.org/au-research/grants/nhmrc/GNT1002592', 'expectedType' => 'purl'],
        ];
        foreach ($tests as $test) {
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

    /** @test * */
    public function it_should_process_nla_parties()
    {
        $tests = [
            ['value' => 'http://nla.gov.au/nla.party-1692395', 'type' => 'uri', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => 'http://nla.gov.au/nla.party-1692395', 'type' => 'nla-party', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => 'http://nla.gov.au/nla.party-1692395', 'type' => 'AU-VANDS', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => 'nla.gov.au/nla.party-1692395', 'type' => 'AU-QGU', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => 'https://nla.gov.au/nla.party-1692395', 'type' => 'AU-QUT', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => 'http://nla.gov.au/nla.party-1692395', 'type' => 'nla.party', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => 'nla.party-1692395', 'type' => 'AU-ANL:PEAU', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => 'nla.party-1692395', 'type' => 'AU-QGU', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => '1692395', 'type' => 'NLA.PARTY', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => 'AU-ANL:PEAU.party-1904955ac1', 'type' => 'AU-ANL:PEAU', 'expectedValue' => 'nla.party-1904955ac1', 'expectedType' => 'AU-ANL:PEAU'],
        ];
        foreach ($tests as $test) {
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

    // IGSN : 10273/  http://igsn.org/

    /** @test * */
    public function it_should_process_igsns()
    {
        $tests = [
            ['value' => 'http://igsn.org/AU1243', 'type' => 'igsn', 'expectedValue' => 'AU1243', 'expectedType' => 'igsn'],
            ['value' => 'https://igsn.org/AU1243', 'type' => 'igsn', 'expectedValue' => 'AU1243', 'expectedType' => 'igsn'],
            ['value' => 'hdl.handle.net/10273/AU1243', 'type' => 'handle', 'expectedValue' => 'AU1243', 'expectedType' => 'igsn'],
            ['value' => '10273/AU1243', 'type' => 'igsn', 'expectedValue' => 'AU1243', 'expectedType' => 'igsn'],
            ['value' => 'au1243', 'type' => 'igsn', 'expectedValue' => 'AU1243', 'expectedType' => 'igsn'],
            ['value' => 'https://igsn.org/AU1243', 'type' => 'uri', 'expectedValue' => 'igsn.org/AU1243', 'expectedType' => 'uri']
        ];
        foreach ($tests as $test) {
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

    /** @test * */
    public function it_should_remove_protocol_from_uri_and_url()
    {
        $tests = [
            ['value' => 'http://geoserver-123.aodn.org.au/geoserver/ncwms?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities', 'type' => 'url', 'expectedValue' => 'geoserver-123.aodn.org.au/geoserver/ncwms?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities', 'expectedType' => 'url'],
            ['value' => 'https://geoserver.imas.utas.edu.au/geoserver/wms?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities', 'type' => 'uri', 'expectedValue' => 'geoserver.imas.utas.edu.au/geoserver/wms?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities', 'expectedType' => 'uri'],
            ['value' => 'http://google.com', 'type' => 'uri', 'expectedValue' => 'google.com', 'expectedType' => 'uri'],
            ['value' => 'http://fish.org', 'type' => 'url', 'expectedValue' => 'fish.org', 'expectedType' => 'url'],
            ['value' => 'fish.org?url="http://google.com', 'type' => 'uri', 'expectedValue' => 'fish.org?url="http://google.com', 'expectedType' => 'uri']
        ];
        foreach ($tests as $test) {
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

    public function testGetIndexableArray()
    {
        $record = $this->stub(RegistryObject::class, ['class' => 'collection']);
        $this->stub(RecordData::class, [
            'registry_object_id' => $record->id,
            'data' => Storage::disk('test')->get('rifcs/collection_all_elements.xml')
        ]);
        $index = IdentifierProvider::getIndexableArray($record);
        $this->assertNotEmpty($index);
        $this->assertNotEmpty($index);
        $this->assertArrayHasKey('identifier_type', $index);
        $this->assertArrayHasKey('identifier_value', $index);
        $this->assertGreaterThan(1, $index['identifier_type']);
        $this->assertSameSize($index['identifier_type'], $index['identifier_value']);
    }

    /** @test * */
    public function it_should_leave_all_other()
    {
        $tests = [
            ['value' => 'http://google.com', 'type' => 'local', 'expectedValue' => 'http://google.com', 'expectedType' => 'local'],
            ['value' => 'https://fish.org', 'type' => 'global', 'expectedValue' => 'https://fish.org', 'expectedType' => 'global'],
            ['value' => 'https://fish.org?url=http://google.com', 'type' => 'noidea', 'expectedValue' => 'https://fish.org?url=http://google.com', 'expectedType' => 'noidea'],
        ];
        foreach($tests as $test){
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }


    /** @test **/
    public function it_should_provide_a_resolvable_url_for_uris()
    {
        $tests = [
            ['value' => 'fish.org', 'type' => 'url'],
            ['value' => 'https://fish.org', 'type' => 'url'],
            ['value' => 'http://fish.org', 'type' => 'uri'],
        ];
        foreach($tests as $test){
            $normalised = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $identifier = IdentifierProvider::format($normalised["value"], $normalised["type"]);
            //var_dump($identifier);
            $this->assertEquals("https://fish.org", $identifier["href"]);
        }
    }

    /** @test * */
    public function it_should_handle_special_cases()
    {
        /**
         * RDA-584 some special case Identifiers found during testing
         */
        $tests = [
            ['value' => 'http://handle.westernsydney.edu.au:8771/2009.7/hiev_104ac1', 'type' => 'handle', 'expectedValue' => 'http://handle.westernsydney.edu.au:8771/2009.7/hiev_104ac1', 'expectedType' => 'handle'],
            ['value' => 'http://www.MyorcidResolver.com.au77ac1', 'type' => 'orcid', 'expectedValue' => 'http://www.MyorcidResolver.com.au77ac1', 'expectedType' => 'orcid'],
            ['value' => 'http://MyAU-ANL:PEAUResolver.com.au88ac1', 'type' => 'AU-ANL:PEAU', 'expectedValue' => 'http://MyAU-ANL:PEAUResolver.com.au88ac1', 'expectedType' => 'AU-ANL:PEAU'],
            ['value' => 'http://www.MypurlResolver.com.aua7896c1', 'type' => 'purl', 'expectedValue' => 'http://www.MypurlResolver.com.aua7896c1', 'expectedType' => 'purl'],
        ];
        foreach($tests as $test){
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

}