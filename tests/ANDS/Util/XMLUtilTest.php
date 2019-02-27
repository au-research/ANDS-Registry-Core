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
        $class = XMLUtil::getRegistryObjectClass($rif_service, $simpleXml);
        $this->assertEquals("service", $class);

    }

}