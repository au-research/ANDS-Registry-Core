<?php

namespace ANDS\Registry;

use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class Relation
 * @package ANDS\Registry
 */
class Relation
{

    private $properties = [];
    private $from = null;
    private $to = null;

    /**
     * @param $key
     * @param $value
     * @return $this
     */
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

    /**
     * @param $key
     * @param $value
     */
    public function replaceProperty($key, $value)
    {
        $this->properties[$key] = $value;
    }

    /**
     * Relation constructor.
     */
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

    /**
     * @param $prop
     * @return null
     */
    public function getProperty($prop)
    {
        return $this->properties[$prop] ?: null;
    }

    /**
     * Alias for getProperty
     *
     * @param $prop
     * @return null
     */
    public function prop($prop)
    {
        return $this->getProperty($prop);
    }

    /**
     * @param $prop
     * @return bool
     */
    public function hasProperty($prop)
    {
        if (array_key_exists($prop, $this->getProperties())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $row
     * @return $this
     */
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

    /**
     * @return static
     */
    public function flip()
    {
        $relation = new static;
        foreach ($this->getProperties() as $key => $value) {
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
        }

        // flip relation_origin
        if ($this->getProperty('to_data_source_id') == $relation->getProperty('from_data_source_id')) {
            $relation->replaceProperty("relation_origin", "REVERSE_INT");
        } else {
            $relation->replaceProperty("relation_origin", "REVERSE_EXT");
        }

        return $relation;
    }

    /**
     * @return $this
     */
    public function getObjects()
    {
        $this->from = RegistryObjectsRepository::getRecordByID($this->getProperty('from_id'));
        $this->to = RegistryObjectsRepository::getRecordByID($this->getProperty('to_id'));
        return $this;
    }

    /**
     * @return null
     */
    public function from()
    {
        if (!$this->from) {
            $this->from = $this->to = RegistryObjectsRepository::getRecordByID($this->getProperty('from_id'));
        }
        return $this->from;
    }

    /**
     * @return null
     */
    public function to()
    {
        if (!$this->to) {
            $this->to = RegistryObjectsRepository::getRecordByID($this->getProperty('to_id'));
        }
        return $this->to;
    }
}