<?php

namespace ANDS\Registry\Providers\RIFCS;


class CitationProviderTest extends \RegistryTestClass
{
    /** @test */
    function it_get_the_citation()
    {
        $record = $this->ensureKeyExist("AUTCollection1");
        $citations = CitationProvider::get($record);
        $this->assertNotNull($citations['citations']);
    }

    /** @test */
    function it_has_bibtex()
    {
        $record = $this->ensureKeyExist("AUTCollection1");
        $citations = CitationProvider::get($record);
        $this->assertNotNull($citations['bibtex']);
        $bibtex = $citations['bibtex'];
        $this->assertRegExp('/title/', $bibtex);
        $this->assertRegExp('/year/', $bibtex);
        $this->assertRegExp('/DOI/', $bibtex);
        $this->assertRegExp('/author/', $bibtex);
        $this->assertRegExp('/publisher/', $bibtex);
    }
}
