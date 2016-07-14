<?php
namespace ANDS\Registry\Connections;

class Relation
{

    private $properties;

    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;
        return $this;
    }

    function __construct()
    {

    }
}