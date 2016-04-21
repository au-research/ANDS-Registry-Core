<?php
/**
 * Class:  ${NAME}
 *
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class TestDOIHelper extends UnitTest
{

    public function testIPRange()
    {
        $this->assertTrue(test_ip('192.168.10.14', '192.168.10.1,192.168.10.15,192.168.10.14'));
    }

    public function setUp() {
        require(APP_PATH.'mydois/helpers/doi_db_helper.php');
    }
}