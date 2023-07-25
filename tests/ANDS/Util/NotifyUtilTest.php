<?php

namespace ANDS\Util;

use PHPUnit\Framework\TestCase;
class NotifyUtilTest  extends TestCase
{

    /**@Test**/
    function test_slack_message(){
        NotifyUtil::sendSlackMessage("Test DEBUG Message from Registry", 999, $message_type='DEBUG');
        NotifyUtil::sendSlackMessage("Test INFO Message from Registry", 99, $message_type='INFO');
        NotifyUtil::sendSlackMessage("Test ERROR Message from Registry", 9, $message_type='ERROR');
    }


}
