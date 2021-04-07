<?php

use ANDS\Registry\Providers\RIFCS\IdentifierProvider;

class IdentifierProviderTest extends \RegistryTestClass
{
    /** @test * */
    public function it_should_process_dois()
    {
        $tests = [
            ['value' => 'DOI:10.234/455', 'type' => 'doi', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            ['value' => 'http://doi.org/10.234/455', 'type' => 'url', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            ['value' => 'https://doi.org/10.234/455','type' => 'uri', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            ['value' => 'https://doi.org/10.234/455','type' => 'doi', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            ['value' => '10.234/455','type' => 'doi', 'expectedValue' => '10.234/455', 'expectedType' => 'doi'],
            // NOT DOIs
            ['value' => '1.234/455', 'type' => 'fish','expectedValue' => '1.234/455', 'expectedType' => 'fish'],
            ['value' => 'http://doi.org/1.234/455','type' => 'url', 'expectedValue' => 'http://doi.org/1.234/455', 'expectedType' => 'url']
        ];
        foreach($tests as $test){
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
            ['value' => 'http://orcid.org/index.php', 'type' => 'url', 'expectedValue' => 'http://orcid.org/index.php', 'expectedType' => 'url'],
            ['value' => 'http://forcid.org/9539-5716', 'type' => 'url', 'expectedValue' => 'http://forcid.org/9539-5716', 'expectedType' => 'url']
        ];
        foreach($tests as $test){
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

    /** @test * */
    public function it_should_process_handles()
    {
        $tests = [
            ['value' => 'http://handle.westernsydney.edu.au:8081/1959.7/512474', 'type' => 'uri', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => 'hdl:1959.7/512474', 'type' => 'handle', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => 'hdl:1959.7/512474', 'type' => 'global', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => 'hdl.handle.net/1959.7/512474', 'type' => 'url', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => 'https://hdl.handle.net/1959.7/512474', 'type' => 'uri', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => 'http://hdl.handle.net/1959.7/512474', 'type' => 'handle', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle'],
            ['value' => '1959.7/512474', 'type' => 'handle', 'expectedValue' => '1959.7/512474', 'expectedType' => 'handle']
        ];
        foreach($tests as $test){
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
        foreach($tests as $test){
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
            ['value' => '1692395', 'type' => 'NLA.PARTY', 'expectedValue' => 'nla.party-1692395', 'expectedType' => 'AU-ANL:PEAU']
        ];
        foreach($tests as $test){
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
            ['value' => 'AU1243', 'type' => 'igsn', 'expectedValue' => 'AU1243', 'expectedType' => 'igsn'],
            ['value' => 'https://igsn.org/AU1243', 'type' => 'uri', 'expectedValue' => 'https://igsn.org/AU1243', 'expectedType' => 'uri']
        ];
        foreach($tests as $test){
            $identifier = IdentifierProvider::getNormalisedIdentifier($test["value"], $test["type"]);
            $this->assertEquals($test["expectedValue"], $identifier["value"]);
            $this->assertEquals($test["expectedType"], $identifier["type"]);
        }
    }

}