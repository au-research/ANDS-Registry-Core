<?php


use ANDS\DOI\Transformer\XMLTransformer;

class XMLTransformerTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_transform_to_kernel_4()
    {
        $xml = file_get_contents(__DIR__."/../assets/geoLocationKernel3.xml");
        $result = XMLTransformer::migrateToKernel4($xml);

        // ensure that geoLocationKernel3 has new fields and it has good values
        $this->assertGreaterThan(0, strpos($result, "<westBoundLongitude>-71.032</westBoundLongitude>"));
        $this->assertGreaterThan(0, strpos($result, "<pointLongitude>-67.302</pointLongitude>"));
        $this->assertGreaterThan(0, strpos($result, "<northBoundLatitude>42.893</northBoundLatitude>"));
    }
}