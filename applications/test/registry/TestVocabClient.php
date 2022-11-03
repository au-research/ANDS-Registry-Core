<?php


namespace ANDS\Test;


class TestVocabClient extends UnitTest
{

    public function testCaching()
    {
        $url = "https://vocabs.ardc.edu.au/repository/api/lda/anzsrc-for/resource.json?uri=http%3A%2F%2Fpurl.org%2Fau-research%2Fvocabulary%2Fanzsrc-for%2F2008%2F2103";
        // $result = $this->ci->vocab->post($url);
        $cacheId = $this->ci->vocab->getCacheID($url);

        if ($this->ci->cache->file->get($cacheId)) {
            $this->ci->cache->file->delete($cacheId);
        }

        //confirm cache is gone
        $this->assertFalse($this->ci->cache->file->get($cacheId));

        //run a post that returns resolving result
        $result = $this->ci->vocab->post($url);

        //confirm cache exist
        $this->assertTrue($this->ci->cache->file->get($cacheId));

        // hit it again and make sure it's the same
        $this->assertEquals($this->ci->vocab->post($url), $result);

        // clean up, delete the cache
        $this->ci->cache->file->delete($cacheId);
    }

    public function testResolveSubject()
    {
        $result = $this->ci->vocab->resolveSubject("2103", "anzsrc-for");
        $this->assertEquals($result["uriprefix"], "http://purl.org/au-research/vocabulary/anzsrc-for/2008/");
        $this->assertEquals($result["notation"], "2103");
        $this->assertEquals($result["value"], "HISTORICAL STUDIES");
        $this->assertEquals($result["about"], "http://purl.org/au-research/vocabulary/anzsrc-for/2008/2103");
    }

    public function testGetBroaderSubjects2103()
    {
        $this->ci->vocab->init()->resolveSubject("2103", "anzsrc-for");
        $result = $this->ci->vocab->getBroaderSubjects("http://purl.org/au-research/vocabulary/anzsrc-for/2008/", "2103");

        //has 1 broader that is 21
        $this->assertEquals(1, sizeof($result));
        $broader = array_values($result)[0];
        $this->assertEquals($broader["notation"], "21");
        $this->assertEquals($broader["value"], "HISTORY AND ARCHAEOLOGY");
    }

    public function testGetBroaderSubjects1505()
    {
        $this->ci->vocab->init()->resolveSubject("1505", "anzsrc-for");
        $result = $this->ci->vocab->getBroaderSubjects("http://purl.org/au-research/vocabulary/anzsrc-for/2008/", "1505");

        //has 1 broader that is 21
        $this->assertEquals(1, sizeof($result));
        $broader = array_values($result)[0];
        $this->assertEquals($broader["notation"], "15");
        $this->assertEquals($broader["value"], "COMMERCE, MANAGEMENT, TOURISM AND SERVICES");
    }

    public function testGetBroaderSubjects060410()
    {
        $this->ci->vocab->init()->resolveSubject("060410", "anzsrc-for");
        $result = $this->ci->vocab->getBroaderSubjects("http://purl.org/au-research/vocabulary/anzsrc-for/2008/", "060410");

        //has 1 broader that is 0604 and 06
        $this->assertEquals(2, sizeof($result));
        $broaders = array_values($result);
        $this->assertEquals($broaders[0]["notation"], "0604");
        $this->assertEquals($broaders[1]["notation"], "06");
    }

    public function setUp()
    {
        $this->ci->load->library('vocab');
    }

}