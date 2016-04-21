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
    }

    /**
     * @name Test IP with HostName
     */
    public function testIPHostName(){
        $this->assertTrue(test_ip('130.56.60.128', 'ands3.anu.edu.au'));
        $this->assertTrue(test_ip('130.56.60.109', 'ands3.anu.edu.au'));
    }

    public function setUp() {
        require(APPS_APP_PATH.'mydois/helpers/doi_db_helper.php');
    }
}