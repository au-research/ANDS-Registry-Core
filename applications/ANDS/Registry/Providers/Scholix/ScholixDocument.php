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
        $str .= "<name>".htmlspecialchars($link['publisher']['name'])."</name>";
        if (array_key_exists('identifier', $link['publisher'])) {
            foreach ($link['publisher']['identifier'] as $identifier) {
                $str .= "<identifiers>";
                $str .= "<identifier>".htmlspecialchars($identifier['identifier'])."</identifier>";
                $str .= "<schema>".htmlspecialchars($identifier['schema'])."</schema>";
                $str .= "</identifiers>";
            }
        }
        $str .= "</publisher>";

        $str .= "<linkProvider>";
        $str .= "<name>".htmlspecialchars($link['linkProvider']['name'])."</name>";
        if (array_key_exists('identifier', $link['linkProvider'])) {
            foreach ($link['linkProvider']['identifier'] as $identifier) {
                $str .= "<identifiers>";
                $str .= "<identifier>" . htmlspecialchars($identifier['identifier']) . "</identifier>";
                $str .= "<schema>" . htmlspecialchars($identifier['schema']) . "</schema>";
                $str .= "</identifiers>";
            }
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
        if (array_key_exists('identifier', $link['source'])) {
            foreach ($link['source']['identifier'] as $identifier) {
                $str .= "<identifier>";
                $str .= "<identifier>" . $identifier['identifier'] . "</identifier>";
                $str .= "<schema>" . $identifier['schema'] . "</schema>";
                $str .= "</identifier>";
            }
        }
        $str .= "<objectType>";
        $str .= "<type>". $link['source']['objectType']."</type>";
        $str .= "</objectType>";
        $str .= "<title>".htmlspecialchars($link['source']['title'])."</title>";

        // creator
        if (array_key_exists('creator', $link['source'])) {
            foreach ($link['source']['creator'] as $creator) {
                $str .= "<creator>";
                $str .= "<creatorName>" . $creator['name'] . "</creatorName>";
                if (array_key_exists('identifier', $creator)) {
                    $str .= "<identifiers>";
                    foreach ($creator['identifier'] as $identifier) {
                        $str .= "<identifier>";
                        $str .= "<identifier>" . $identifier['identifier'] . "</identifier>";
                        $str .= "<schema>" . $identifier['schema'] . "</schema>";
                        $str .= "</identifier>";
                    }
                    $str .= "</identifiers>";
                }
                $str .= "</creator>";
            }
        }

        $str .= "<publicationDate>". $link['source']['publicationDate'] . "</publicationDate>";

        $str .= "</source>";

        // target
        $str .= "<target>";
        if (array_key_exists('identifier', $link['target'])) {
            foreach ($link['target']['identifier'] as $identifier) {
                $str .= "<identifier>";
                $str .= "<identifier>" . htmlspecialchars($identifier['identifier']) . "</identifier>";
                $str .= "<schema>" . $identifier['schema'] . "</schema>";
                $str .= "</identifier>";
            }
        }
        $str .= "<objectType>";
        $str .= "<type>". $link['target']['objectType']."</type>";
        $str .= "</objectType>";
        if (array_key_exists('title', $link['target'])) {
            $str .= "<title>".htmlspecialchars($link['target']['title'])."</title>";
        }

        // creator
        if (array_key_exists('creator', $link['target'])) {
            foreach ($link['target']['creator'] as $creator) {
                $str .= "<creator>";
                $str .= "<creatorName>" . $creator['name'] . "</creatorName>";
                if (array_key_exists('identifier', $creator)) {
                    $str .= "<identifiers>";
                    foreach ($creator['identifier'] as $identifier) {
                        $str .= "<identifier>";
                        $str .= "<identifier>" . $identifier['identifier'] . "</identifier>";
                        $str .= "<schema>" . $identifier['schema'] . "</schema>";
                        $str .= "</identifier>";
                    }
                    $str .= "</identifiers>";
                }
                $str .= "</creator>";
            }
        }

        $str .= "<publicationDate>". $link['source']['publicationDate'] . "</publicationDate>";

        $str .= "</target>";

        $str .= "</link>";
        return $str;
    }

    public function getLinks()
    {
        return $this->links;
    }

}