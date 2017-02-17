<?php


namespace ANDS\Registry\Providers\Scholix;


class ScholixDocument
{
    public $properties = [];

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
}