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
    public function test_index(){
        $record = $this->ensureKeyExist("C1_46");
        $citation_info_search = CitationProvider::getIndexableArray($record);
        $this->assertEquals("10.123123123R Citation Title V0.2 Australia Some Example Publisher http://www.exapmle.com Context Joe Blogs 12-12-2001" , $citation_info_search["citation_info_search"][0]);
    }

}
