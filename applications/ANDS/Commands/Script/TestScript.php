<?php


namespace ANDS\Commands\Script;


class TestScript extends GenericScript
{
    public function run()
    {
        $this->log("Script ran");
        $this->log('finished');
    }
}