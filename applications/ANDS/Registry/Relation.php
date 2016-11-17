<?php

namespace ANDS\Registry;

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

    /**
     * Takes in a mapping, returns an array display with the mapping applied
     *
     * @param $mapping
     * @return array
     */
    public function format($mapping = [])
    {
        $result = [];

        foreach ($this->getProperties() as $key=>$value) {
            if (array_key_exists($key, $mapping)) {
                $result[$mapping[$key]] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function flip()
    {
        $relation = new static;
        foreach ($this->getProperties() as $key=>$value) {

            // if it starts with from
            if (strpos($key, 'from_') === 0) {
                $replace = str_replace("from_", "to_", $key);
                $relation->setProperty($replace, $value);
            } elseif (strpos($key, 'to_') === 0) {
                $replace = str_replace("to_", "from_", $key);
                $relation->setProperty($replace, $value);
            } else {
                $relation->setProperty($key, $value);
            }

            // @todo flip relation_type as well
        }

        return $relation;
    }
}