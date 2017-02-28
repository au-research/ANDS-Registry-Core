<?php


namespace ANDS\Registry\Providers\Scholix;


use ANDS\API\Task\ImportSubTask\ProcessDelete;
use Carbon\Carbon;

class ScholixDocument
{
    public $properties = [];
    public $links = [];
    public $record;

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

    public function toOAI()
    {
        $xml = "";

        foreach ($this->links as $index => $link) {
            $identifier = "oai:ands.org.au::" . $this->record->registry_object_id. "-".($index+1);
            $date = Carbon::now()->format('Y-m-d\TH:i:s\Z');
            $dataSource = "dataSource:".$this->record->datasource->slug;
            $group = str_replace(" ", "0x20", $this->record->group);
            $xml .= "<record>";
            $xml .= "<header>
                <identifier>$identifier</identifier>
                <datestamp>$date</datestamp>
                <setSpec>$dataSource</setSpec>
                <setSpec>class:{$this->record->class}</setSpec>
                <setSpec>group:$group</setSpec>
            </header>";
            $xml .= "<metadata>";
            $xml .= $this->json2xml($link['link']);
            $xml .= "</metadata>";
            $xml .= "</record>";
        }
        return $xml;
    }

    public function toXML($wrapper = "links")
    {
        $xml = "";
        if ($wrapper) {
            $xml .= "<$wrapper>";
        }

        foreach ($this->links as $link) {
            $xml .= $this->json2xml($link['link']);
        }

        if ($wrapper) {
            $xml .= "</$wrapper>";
        }
        return $xml;
    }

    public function json2xml($link)
    {
        $str = "<link xmlns=\"http://www.scholix.org\"
 xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
 xsi:schemaLocation=\"http://www.scholix.org file:/Users/sandro/Desktop/scholix1.xsd\">";

        $str .= "<publicationDate>".$link['publicationDate']."</publicationDate>";

        $str .= "<publisher>";
        $str .= "<name>".$link['publisher']['name']."</name>";
        foreach ($link['publisher']['identifier'] as $identifier) {
            $str .= "<identifiers>";
            $str .= "<identifier>".$identifier['identifier']."</identifier>";
            $str .= "<schema>".$identifier['schema']."</schema>";
            $str .= "</identifiers>";
        }
        $str .= "</publisher>";

        $str .= "<linkProvider>";
        $str .= "<name>".$link['linkProvider']['name']."</name>";
        foreach ($link['linkProvider']['identifier'] as $identifier) {
            $str .= "<identifiers>";
            $str .= "<identifier>".$identifier['identifier']."</identifier>";
            $str .= "<schema>".$identifier['schema']."</schema>";
            $str .= "</identifiers>";
        }
        $str .= "</linkProvider>";

        if (array_key_exists('relationship', $link)) {
            foreach ($link['relationship'] as $relationship) {
                $str .= "<relationship>";
                $str .= "<name>".$relationship['name']."</name>";
                $str .= "<schema>".$relationship['schema']."</schema>";
                $str .= "<inverseRelationship>".$relationship['inverse']."</inverseRelationship>";
                $str .= "</relationship>";
            }
        }

        // source
        $str .= "<source>";
        foreach ($link['source']['identifier'] as $identifier) {
            $str .= "<identifier>";
            $str .= "<identifier>".$identifier['identifier']."</identifier>";
            $str .= "<schema>".$identifier['schema']."</schema>";
            $str .= "</identifier>";
        }
        $str .= "<objectType>";
        $str .= "<type>". $link['source']['objectType']."</type>";
        $str .= "</objectType>";
        $str .= "<title>".$link['source']['title']."</title>";
        $str .= "</source>";

        // target
        $str .= "<target>";
        foreach ($link['target']['identifier'] as $identifier) {
            $str .= "<identifier>";
            $str .= "<identifier>".$identifier['identifier']."</identifier>";
            $str .= "<schema>".$identifier['schema']."</schema>";
            $str .= "</identifier>";
        }
        $str .= "<objectType>";
        $str .= "<type>". $link['target']['objectType']."</type>";
        $str .= "</objectType>";
        if (array_key_exists('title', $link['target'])) {
            $str .= "<title>".$link['target']['title']."</title>";
        }
        $str .= "</target>";

        $str .= "</link>";
        return $str;
    }

    public function getLinks()
    {
        return $this->links;
    }

}