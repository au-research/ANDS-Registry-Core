<?php


namespace ANDS\RegistryObject;


trait ExportToCSVTrait
{
    public function toCSV()
    {
        return [
            "roId:ID" => $this->id,
            ":LABEL" => implode(";", ["RegistryObject", $this->class, $this->type]),
            "key" => $this->key,
            "type" => $this->type,
            "group" => $this->group,
            "slug" => $this->slug,
            "url" => $this->portalUrl,
            "data_source_id" => $this->data_source_id,
            "title" => $this->sanitizeTitle($this->title),
            "record_owner" => $this->record_owner
        ];
    }

    /**
     * Sanitize a title
     * TODO: Refactor to helper
     *
     * @param $title
     * @return mixed
     */
    private function sanitizeTitle($title)
    {
        $title = str_replace([',', '"', ';', '\t', ':'], '', $title);
        $title = preg_replace( "/\r|\n/", " ", $title);
        return $title;
    }
}