<?php

namespace ANDS\Registry;

use ANDS\RegistryObject;
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
    protected $multiValued = ['relation_type', 'relation_origin'];

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setProperty($key, $value)
    {
        if (!$this->hasProperty($key)) {
            $this->properties[$key] = $value;
            return $this;
        }

        // deal with multiValued field
        if (in_array($key, $this->multiValued)) {
            if (is_array($this->getProperty($key))) {
                array_push($this->properties[$key], $value);
                return $this;
            }
            $this->properties[$key] = [
                $this->properties[$key], $value
            ];
            return $this;
        }

        // normal fields
        if (is_array($this->getProperty($key)) && !in_array($value, $this->properties[$key])) {
            array_push($this->properties[$key], $value);
            return $this;
        }

        if ($this->properties[$key] != $value) {
            $this->properties[$key] = [
                $this->properties[$key],
                $value
            ];
            return $this;
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
    function __construct($properties = [])
    {
        foreach ($properties as $key => $value) {
            $this->setProperty($key, $value);
        }
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
        return array_key_exists($prop, $this->properties) ? $this->properties[$prop]: null;
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
        $validMultiValued = ['relation_type', 'relation_origin'];
        foreach ($row as $key => $value) {
            if (in_array($key, $validMultiValued)) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $this->setProperty($key, $val);
                    }
                } else {
                    $this->setProperty($key, $value);
                }
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
    public function format($mapping = [], $removedUnmapped = false)
    {
        $result = [];

        foreach ($this->getProperties() as $key => $value) {
            if (array_key_exists($key, $mapping)) {
                $result[$mapping[$key]] = $value;
            } else {
                if ($removedUnmapped == false) {
                    $result[$key] = $value;
                }
            }

            if ($key == "children" && is_array($value)) {
                foreach ($value as $valueKey=>&$child) {
                    $child = $child->format($mapping, $removedUnmapped);
                }
                $value = array_values($value);
                $result[$key] = $value;
            }

        }

        return $result;
    }

    /**
     * Return an unique ID for the given relation
     *
     * @return null|string
     */
    public function getUniqueID()
    {
        if ($this->prop('to_key')) {
            return md5($this->prop('from_key').$this->prop('to_key'));
        }

        if ($this->prop('to_identifier')) {
            return md5($this->prop('from_key').$this->prop('to_identifier'));
        }

        return uniqid();
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

        // flip relation origin
        $origin = $relation->prop('relation_origin');
        switch ($origin) {
            case "IDENTIFIER":
                $relationOrigin = "REVERSE_IDENTIFIER";
                break;
            case "GRANTS":
                $relationOrigin = "REVERSE_GRANTS";
                break;
            case "EXPLICIT":
                if ($relation->prop('to_data_source_id') == $relation->prop('from_data_source_id')) {
                    $relationOrigin = "REVERSE_INT";
                } else {
                    $relationOrigin = "REVERSE_EXT";
                }
                break;
            default:
                $relationOrigin = "REVERSE";
                break;
        }
        $relation->replaceProperty("relation_origin", $relationOrigin);

        // flip relation_type
//        $relation_type = $relation->prop('relation_type');
//        if (is_array($relation_type)) {
//            $relation_type = array_first($relation_type);
//        }
//        $relation->replaceProperty(
//            "relation_type", getReverseRelationshipString($relation_type)
//        );

        return $relation;
    }

    /**
     * Switch the from record out to another
     * Mainly used for duplicate records
     *
     * @param RegistryObject $record
     * @return static
     */
    public function switchFromRecord(RegistryObject $record)
    {
        $relation = new static;
        foreach ($this->getProperties() as $key => $value) {
            // if it starts with from
            if (strpos($key, 'from_') === 0) {
                $keyValue = str_replace("from_", "", $key);
                $replace = $record->getAttribute($keyValue);
                if ($keyValue == "id") {
                    $replace = $record->registry_object_id;
                }
                $relation->setProperty($key, $replace);
            } else {
                $relation->setProperty($key, $value);
            }
        }
        return $relation;
    }

    /**
     * Switch the to record out to another
     * Mainly used for duplicate records
     *
     * @param RegistryObject $record
     * @return static
     */
    public function switchToRecord(RegistryObject $record)
    {
        $relation = new static;
        foreach ($this->getProperties() as $key => $value) {
            // if it starts with from
            if (strpos($key, 'to_') === 0) {
                $keyValue = str_replace("to_", "", $key);
                $replace = $record->getAttribute($keyValue);
                if ($keyValue == "id") {
                    $replace = $record->registry_object_id;
                }
                $relation->setProperty($key, $replace);
            } else {
                $relation->setProperty($key, $value);
            }
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

    public function isRelatesToIdentifier()
    {
        if ($this->prop('relation_origin') == "IDENTIFIER") {
            return true;
        }
        return false;
    }

    public function hasRelationType($type)
    {
        if (is_array($this->prop('relation_type'))) {
            foreach ($this->prop('relation_type') as $rtype) {
                if ($type === $rtype) {
                    return true;
                }
            }
            return false;
        }

        return $type == $this->prop('relation_type');
    }

    public function hasRelationTypes($types)
    {
        foreach ($types as $type) {
            if ($this->hasRelationType($type)) {
                return true;
            }
        }
        return false;
    }

    public function isReverse()
    {
        $origins = $this->prop('relation_origin');
        if (!is_array($origins)) {
            return strpos($this->prop('relation_origin'), "REVERSE") !== false;
        }
        foreach ($origins as $origin) {
            if (strpos($origin, "REVERSE")) {
                return true;
            }
        }
        return false;
    }
}