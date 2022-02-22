<?php


namespace ANDS\Commands\Script;


use ANDS\Log\Log;

class TestScript extends GenericScript
{
    public function run()
    {
        Log::init();
//        Log::info("test", ['stuff' => 'something', 'q' => 'fish']);
//        Log::debug("A debug message");

        Log::info("A message from me", ['caller' => __METHOD__]);
    }
}