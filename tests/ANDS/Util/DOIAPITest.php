<?php

namespace ANDS\Util;

use PHPUnit\Framework\TestCase;

class DOIAPITest extends TestCase
{


    public function testStub()
    {
        $dataciteDOI = "10.22004/ag.econ.295222";
        $crossrefDOI = "10.23943/princeton/9780691143972.003.0001";
    }
    public function Resolve()
    {
        $dataciteDOI = "10.22004/ag.econ.295222";
        $crossrefDOI = "10.23943/princeton/9780691143972.003.0001";

        $result = DOIAPI::resolve($dataciteDOI);
        $this->assertNotNull($result);
        $this->assertEquals("Plants, Handlers, and Bulk Tank Units Under the New York-New Jersey Marketing Orders", $result['title']);

        $result = DOIAPI::resolve($crossrefDOI);
        $this->assertNotNull($result);
        $this->assertNotEmpty($result['source']);
    }

    public function ResolveDOIContentNegotiation()
    {
        $dataciteDOI = "10.22004/ag.econ.295222";
        $crossrefDOI = "10.23943/princeton/9780691143972.003.0001";

        $result = DOIAPI::resolveDOIContentNegotiation($dataciteDOI);
        $this->assertNotNull($result, "DataCite DOI is resolvable via Content Negotiation");

        $result = DOIAPI::resolveDOIContentNegotiation($crossrefDOI);
        $this->assertNotNull($result, "CrossRef DOI is resolvable via Content Negotiation");
    }

    public function unresolvable()
    {
        $dataciteDOI = "10.22004/unresolvable";

        $result = DOIAPI::resolveDOIContentNegotiation($dataciteDOI);
        $this->assertNull($result);
    }


}
