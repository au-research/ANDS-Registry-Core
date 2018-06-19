<?php


namespace ANDS\RegistryObject;


use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Util\StrUtil;

trait ExportToCSVTrait
{
    public static $CSV_NEO_GRAPH = "graph";
    public static $CSV_RESEARCH_GRAPH = "researchgraph";

    public function toCSV($type = null)
    {
        switch ($type) {
            case static::$CSV_RESEARCH_GRAPH:
                return $this->researchGraph();
            case static::$CSV_NEO_GRAPH:
            default:
                return $this->graph();
        }
    }

    /**
     * Exportable array for researchgraph
     *
     * @return array
     */
    private function researchGraph()
    {
        if ($this->type === "publication") {
            $type = 'publication';
        } elseif ($this->type === "person") {
            $type = 'researcher';
        } elseif ($this->type === 'grant') {
            $type = 'grant';
        } else {
            $type = 'auxilary';
        }

        return [
            'key:ID' => $this->getResearchGraphID(),
            'source' => 'ands.org.au',
            'local_id' => $this->key,
            'title' => StrUtil::sanitize($this->title),
            'author_list' => '',
            'last_updated' => $this->getRegistryObjectAttributeValue('updated'),
            'publication_year' => DatesProvider::getPublicationDate($this),
            'url' => $this->portal_url,
            'type' => $type,
            'ands_class' => $this->class,
            'ands_type' => $this->type,
            'ands_data_source_id' => $this->data_source_id
        ];
    }

    public function getResearchGraphID()
    {
        return 'researchgraph.org/ands/'.$this->id;
    }

    /**
     * Exportable array for standard internal neo4j consumption
     *
     * @return array
     */
    private function graph()
    {
        return [
            "roId:ID" => (string) $this->id,
            ":LABEL" => implode(";", ["RegistryObject", $this->class, $this->type]),
            "key" => $this->key,
            "class" => $this->class,
            "type" => $this->type,
            "group" => $this->group,
            "slug" => $this->slug,
            "data_source_id" => $this->data_source_id,
            "title" => StrUtil::sanitize($this->title),
            "record_owner" => $this->record_owner,
            "modified_at" => $this->modified_at,
            "created_at" => $this->created_at
        ];
    }
}