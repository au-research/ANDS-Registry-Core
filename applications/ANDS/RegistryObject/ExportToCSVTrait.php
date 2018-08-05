<?php


namespace ANDS\RegistryObject;


use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
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

        // TODO: doi
        // TODO: orcid

        return [
            'key:ID' => static::researchGraphID($this->id),
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
            'ands_data_source_id' => $this->data_source_id,
            'doi' => collect($this->registryObjectIdentifiers)->filter(function($identifier) {
                return $identifier->identifier_type === "doi";
            })->pluck('identifier')->first() ?: '',
            'orcid' => collect($this->registryObjectIdentifiers)->filter(function($identifier) {
                return $identifier->identifier_type === "orcid";
            })->pluck('identifier')->first() ?: '',
        ];
    }

    public static function researchGraphID($id)
    {
        return 'researchgraph.org/ands/'. $id;
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
            ":LABEL" => implode(";", ["RegistryObject", "`{$this->class}`", "`$this->type`"]),
            "key" => StrUtil::removeNewlines($this->key),
            "class" => $this->class,
            "type" => $this->type,
            "group" => StrUtil::removeNewlines($this->group),
            "slug" => $this->slug,
            "data_source_id" => $this->data_source_id,
            "title" => StrUtil::sanitize($this->title),
            "record_owner" => StrUtil::removeNewlines($this->record_owner),
            "modified_at" => $this->modified_at,
            "created_at" => $this->created_at
        ];
    }
}