<?php

namespace ANDS\Test;

class TestEngineHelper extends UnitTest
{

    /**
     * @name Sample Test Case
     * @note Some note to go under here
     */
    public function test1()
    {
        $this->assertTrue(1 + 1);
        $this->assertTrue(true);
    }

    public function test2()
    {
        $this->assertTrue(true);
    }

    /**
     * @name Test get_config_item
     * @note base_url must equals to config
     */
    public function test_get_config_item()
    {
        $config = get_config_item('base_url');
        $this->assertEquals($config, "http://minhdev.ands.org.au/test/22");
    }

}