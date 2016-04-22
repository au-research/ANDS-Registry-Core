<?php
/**
 * Class:  ${NAME}
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
namespace ANDS\Test;

class TestDOIHelper extends UnitTest
{

    /**
     * @name Test IP Range
     */
    public function testIPRange()
    {
        $this->assertTrue(test_ip('192.168.10.14', '192.168.10.1,192.168.10.15,192.168.10.14'));
        $this->assertTrue(test_ip('192.168.10.14', '168.168.10.1-192.168.10.15'));
        $this->assertTrue(test_ip('130.56.111.71', '130.56.60.97-130.56.111.71'));
        $this->assertTrue(test_ip('127.0.0.1', '127.0.0.1-194.123.123.123'));
    }

    /**
     * @name Test IP with HostName
     */
    public function testIPHostName()
    {
        $this->assertTrue(test_ip('130.56.60.128', 'ands3.anu.edu.au'));
        $this->assertFalse(test_ip('130.56.60.109', 'ands3.anu.edu.au'));
        $this->assertFalse(test_ip('hello world!#$%$#%', '130.56.62.129'));
    }

    /**
     * @name Test IP exact matching
     */
    public function testExactMatching()
    {
        $this->assertTrue(test_ip('127.0.0.1', '127.0.0.1'));
        $this->assertTrue(test_ip('1.2.3.5', '1.2.3.5'));
        $this->assertFalse(test_ip('1.x.x.x', 'test+str'));
        $this->assertFalse(test_ip('127.0.0.1', '127.0.0.2'));
    }

    /**
     * @name Test IP belongs to a CIDR range
     */
    public function testCIDR()
    {
        $this->assertTrue(test_ip('192.168.1.23', '192.168.1.0/24'));
        $this->assertTrue(test_ip('192.168.1.4', '192.168.1.4/32'));
        $this->assertTrue(test_ip('192.168.1.23', '192.168.1.0/24'));
        $this->assertFalse(test_ip('192.168.1.5', '192.168.1.4/32'));
        $this->assertFalse(test_ip('92.168.4.23', '192.162.1.0/24'));
        $this->assertFalse(test_ip('192.168.1.23', '192.162.1.0/24'));
    }

    public function setUp()
    {
        require(APPS_APP_PATH . 'mydois/helpers/doi_db_helper.php');
    }
}