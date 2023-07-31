<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 28/2/19
 * Time: 9:43 AM
 */
use \ANDS\Util\XMLUtil;

class XMLUtilTest extends RegistryTestClass
{

    /**@Test**/
    public function test_get_class_from_xml()
    {
        $rif_service = file_get_contents("tests/resources/rifcs/service_quality.xml");

        $class = XMLUtil::getRegistryObjectClass($rif_service);
        $this->assertEquals("service", $class);

        $simpleXml = XMLUtil::getSimpleXMLFromString($rif_service);
        $class = XMLUtil::getRegistryObjectClass(null, $simpleXml);
        $this->assertEquals("service", $class);

    }

    /**@Test**/
    public function test_string_content_from_xml()
    {
        $rif_service = file_get_contents("tests/resources/rifcs/collection_all_elements.xml");

        $text_content = XMLUtil::getTextContent($rif_service, "relatedInfo");

        $this->assertGreaterThan(7, sizeof($text_content));

    }


}