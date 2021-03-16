<?php

use ANDS\Registry\Providers\RIFCS\IdentifierProvider;

class IdentifierProviderTest extends \RegistryTestClass
{
    /** @test * */
    public function it_should_process_dois()
    {
        $tests = [
            ['input' => 'DOI:10.234/455', 'expected' => '10.234/455'],
            ['input' => 'http://doi.org/10.234/455', 'expected' => '10.234/455'],
            ['input' => 'https://doi.org/10.234/455', 'expected' => '10.234/455'],
            ['input' => '10.234/455', 'expected' => '10.234/455'],
            // NOT DOIs
            ['input' => '1.234/455', 'expected' => '1.234/455'],
            ['input' => 'http://doi.org/1.234/455', 'expected' => 'http://doi.org/1.234/455']
        ];
        foreach($tests as $test){
            $this->assertEquals($test["expected"], IdentifierProvider::getNormalisedIdentifier($test["input"], "doi"));
        }
    }

}