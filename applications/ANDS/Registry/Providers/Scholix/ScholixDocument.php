<?php


namespace ANDS\Registry\Providers\Scholix;


use ANDS\API\Task\ImportSubTask\ProcessDelete;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

class ScholixDocument
{
    public $properties = [];
    public $links = [];
    public $record;
    protected $linkIDPrefix = "oai:ands.org.au::";

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
            $identifier = $this->getLinkIdentifier($link);
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

    public function getLinkIdentifier($link)
    {
        $prefix = $this->linkIDPrefix;
        $sourceID = $link['link']['source']['identifier'][0]['identifier'];
        $targetID = $link['link']['target']['identifier'][0]['identifier'];

        $namespace = Uuid::uuid5(Uuid::NAMESPACE_URL, "https://researchdata.ands.org.au")->toString();
        $uuid5 = Uuid::uuid5($namespace, $sourceID . $targetID)->toString();

        $identifier = $prefix . $uuid5;
        return $identifier;
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

    /**
     * Returns the schema v3 for the scholix
     *
     * CC-2150
     *
     * @url https://github.com/scholix/schema/blob/master/xsd/v3/schema.xsd
     * @param $link
     * @return string
     */
    public function schema3($link)
    {
        $str = "<scholix xmlns=\"http://www.scholix.org\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.scholix.org\">";

        $str .= "<LinkPublicationDate>".$link['publicationDate']."</LinkPublicationDate>";

        $str .= "<LinkProvider>";
        $str .= "<name>Australian National Data Service</name>";
        if (array_key_exists('identifier', $link['linkProvider'])) {
            foreach ($link['linkProvider']['identifier'] as $identifier) {
                $str .= "<identifier>";
                $str .= "<ID>" . htmlspecialchars($identifier['identifier']) . "</ID>";
                $str .= "<IDScheme>" . htmlspecialchars($identifier['schema']) . "</IDScheme>";
                $str .= "</identifier>";
            }
        }
        $str .= "</LinkProvider>";

        if (array_key_exists('relationship', $link)) {
            foreach ($link['relationship'] as $relationship) {
                $str .= "<RelationshipType>";
                $str .= "<Name>".$relationship['name']."</Name>";
                $str .= "<Schema>".$relationship['schema']."</Schema>";
                $str .= "</RelationshipType>";
            }
        }

        // source
        $str .= "<source>";
        if (array_key_exists('identifier', $link['source'])) {
            foreach ($link['source']['identifier'] as $identifier) {
                $str .= "<Identifier>";
                $str .= "<ID>" . $identifier['identifier'] . "</ID>";
                $str .= "<IDScheme>" . $identifier['schema'] . "</IDScheme>";
                $str .= "</Identifier>";
            }
        }
        $str .= "<Type>";
        $str .= "<type>". $link['source']['objectType']."</type>";
        $str .= "</Type>";
        $str .= "<Title>".htmlspecialchars($link['source']['title'])."</Title>";

        // creator
        if (array_key_exists('creator', $link['source'])) {
            foreach ($link['source']['creator'] as $creator) {
                $str .= "<Creator>";
                $str .= "<Name>" . $creator['name'] . "</Name>";
                if (array_key_exists('identifier', $creator)) {
                    foreach ($creator['identifier'] as $identifier) {
                        $str .= "<Identifier>";
                        $str .= "<ID>" . $identifier['identifier'] . "</ID>";
                        $str .= "<IDScheme>" . $identifier['schema'] . "</IDScheme>";
                        $str .= "</Identifier>";
                    }
                }
                $str .= "</Creator>";
            }
        }

        if (array_key_exists('publicationDate', $link['source'])) {
            $str .= "<PublicationDate>" . $link['source']['publicationDate'] . "</PublicationDate>";
        }

        $str .= "<publisher>";
        $str .= "<name>".htmlspecialchars($link['publisher']['name'])."</name>";
        if (array_key_exists('identifier', $link['publisher'])) {
            foreach ($link['publisher']['identifier'] as $identifier) {
                $str .= "<identifier>";
                $str .= "<ID>".htmlspecialchars($identifier['identifier'])."</ID>";
                $str .= "<IDSheme>".htmlspecialchars($identifier['schema'])."</IDSheme>";
                //$str .= "<IDURL></IDURL>";
                $str .= "</identifier>";
            }
        }
        $str .= "</publisher>";

        $str .= "</source>";

        // target
        $str .= "<target>";
        if (array_key_exists('identifier', $link['target'])) {
            foreach ($link['target']['identifier'] as $identifier) {
                $str .= "<Identifier>";
                $str .= "<ID>" . htmlspecialchars($identifier['identifier']) . "</ID>";
                $str .= "<IDScheme>" . $identifier['schema'] . "</IDScheme>";
                $str .= "</Identifier>";
            }
        }
        $str .= "<Type>";
        $str .= "<type>". $link['target']['objectType']."</type>";
        $str .= "</Type>";
        if (array_key_exists('title', $link['target'])) {
            $str .= "<Title>".htmlspecialchars($link['target']['title'])."</Title>";
        }

        // creator
        if (array_key_exists('creator', $link['target'])) {
            foreach ($link['target']['creator'] as $creator) {
                $str .= "<Creator>";
                $str .= "<Name>" . $creator['name'] . "</Name>";
                if (array_key_exists('identifier', $creator)) {
                    foreach ($creator['identifier'] as $identifier) {
                        $str .= "<Identifier>";
                        $str .= "<ID>" . $identifier['identifier'] . "</ID>";
                        $str .= "<Scheme>" . $identifier['schema'] . "</Scheme>";
                        $str .= "</Identifier>";
                    }
                }
                $str .= "</Creator>";
            }
        }

        if (array_key_exists('publicationDate', $link['target'])) {
            $str .= "<PublicationDate>" . $link['target']['publicationDate'] . "</PublicationDate>";
        }

        $str .= "<publisher>";
        $str .= "<name>".htmlspecialchars($link['publisher']['name'])."</name>";
        if (array_key_exists('identifier', $link['publisher'])) {
            foreach ($link['publisher']['identifier'] as $identifier) {
                $str .= "<identifier>";
                $str .= "<ID>".htmlspecialchars($identifier['identifier'])."</ID>";
                $str .= "<IDSheme>".htmlspecialchars($identifier['schema'])."</IDSheme>";
                //$str .= "<IDURL></IDURL>";
                $str .= "</identifier>";
            }
        }
        $str .= "</publisher>";

        $str .= "</target>";


        $str .= "</scholix>";

        return $str;
    }

    /**
     * Returns the schema v1 for the Scholix
     *
     * @note deprecated by CC-2150 in favor for schema version 3
     * @url https://github.com/scholix/schema/blob/master/xsd/v1/scholix.xsd
     * @param $link
     * @return string
     */
    public function schema1($link)
    {
        $str = "<link>";

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

        if (array_key_exists('publicationDate', $link['source'])) {
            $str .= "<publicationDate>" . $link['source']['publicationDate'] . "</publicationDate>";
        }

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

        if (array_key_exists('publicationDate', $link['target'])) {
            $str .= "<publicationDate>" . $link['target']['publicationDate'] . "</publicationDate>";
        }

        $str .= "</target>";

        $str .= "</link>";
        return $str;
    }

    public function json2xml($link)
    {
        return $this->schema3($link);
    }

    public function getLinks()
    {
        return $this->links;
    }

}