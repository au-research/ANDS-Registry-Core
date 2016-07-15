<?php
namespace ANDS\Registry\Connections;

class Relation
{

    private $properties = [];

    public function setProperty($key, $value)
    {
        if ($this->hasProperty($key)) {
            if (is_array($this->getProperty($key))) {
                if (!in_array($value, $this->properties[$key])) {
                    array_push($this->properties[$key], $value);
                }
            } elseif ($this->properties[$key] != $value) {
                $this->properties[$key] = array($this->properties[$key], $value);
            }
        } else {
            $this->properties[$key] = $value;
        }
        return $this;
    }

    function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getProperties()
    {
        return $this->properties;
    }

    public function getProperty($prop)
    {
        return $this->properties[$prop] ?: null;
    }

    public function hasProperty($prop)
    {
        if (array_key_exists($prop, $this->getProperties())) {
            return true;
        } else {
            return false;
        }
    }

    public function mergeWith($row)
    {
        foreach ($row as $key => $value) {
            if ($this->hasProperty($key) && $this->getProperty($key) != $value) {
                $this->setProperty($key, $value);
            }
        }
        return $this;
    }
}