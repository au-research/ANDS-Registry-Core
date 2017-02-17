<?php


namespace ANDS\Registry\Providers\Scholix;


class ScholixDocument
{
    public $properties = [];

    /**
     * @param $prop
     * @param array $value
     * @return $this
     */
    public function set($prop, $value = [])
    {
        $this->properties[$prop] = $value;
        return $this;
    }

    /**
     * @param $prop
     * @return mixed|null
     */
    public function prop($prop)
    {
        return array_key_exists($prop, $this->properties) ? $this->properties[$prop] : null;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    public function toArray()
    {
        return $this->properties;
    }

    public function toJson()
    {
        return json_encode($this->properties, true);
    }

    public function toXML()
    {
        return "";
    }
}