<?php
namespace ANDS;

class RegistryPlugin
{
    protected $ro;
    public function __construct($ro = false)
    {
        if ($ro) $this->ro = $ro;
    }

    public function injectRo($ro) {
        $this->ro = $ro;
    }

}
