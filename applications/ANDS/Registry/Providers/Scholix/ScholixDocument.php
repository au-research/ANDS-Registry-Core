<?php


namespace ANDS\Registry\Providers\Scholix;


class ScholixDocument
{
    public $properties = [];
    public $links = [];

    public function addLink($link)
    {
        $this->links[] = ['link'=> $link];
    }

    /**
     * @param $key
     * @param array $value
     * @return $this
     */
    public function set($key, $value = [])
    {
        if ($this->hasProperty($key)) {
            if (is_array($this->getProperty($key))) {
                if (!in_array($value, $this->properties[$key])) {
                    array_push($this->properties[$key], $value);
                }
            } elseif ($this->properties[$key] != $value) {
                $this->properties[$key] = [$this->properties[$key], $value];
            }
        } else {
            $this->properties[$key] = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    private function hasProperty($key)
    {
        if (array_key_exists($key, $this->getProperties())) {
            return true;
        } else {
            return false;
        }
    }

    private function getProperty($key)
    {
        return array_key_exists($key, $this->properties) ? $this->properties[$key]: null;
    }

    /**
     * @param $prop
     * @return mixed|null
     */
    public function prop($prop)
    {
        return $this->getProperty($prop);
    }

    public function toArray()
    {
        return $this->links;
    }

    public function toJson()
    {
        return json_encode($this->links, true);
    }

    public function toXML()
    {
        return "<link></link>";
    }

}