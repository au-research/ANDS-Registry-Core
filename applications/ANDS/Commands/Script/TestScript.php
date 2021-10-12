<?php


namespace ANDS\Commands\Script;


class TestScript extends GenericScript
{
    public function run()
    {
        $this->log("Script ran", "info");
        $this->log("Warning", "warning");
        $this->log("Debug", "debug");
        $this->log("Error", "error");
        $this->log('finished');
    }
}