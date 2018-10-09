<?php


namespace ANDS\OAI;


class Record
{

    private $identifier = null;
    private $datestamp = null;
    private $specs = [];
    private $metadata = null;

    /**
     * Record constructor.
     * @param null $identifier
     * @param $datestamp
     */
    public function __construct($identifier, $datestamp = null)
    {
        $this->identifier = $identifier;
        $this->datestamp = $datestamp;
    }

    /**
     * @param array $specs
     * @return Record
     */
    public function setSpecs($specs)
    {
        $this->specs = $specs;
        return $this;
    }

    /**
     * @param null $metadata
     * @return Record
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function toArray()
    {
        return [
            'identifier' => $this->identifier,
            'datestamp' => $this->datestamp,
            'specs' => $this->specs,
            'metadata' => $this->metadata
        ];
    }

    public function addSet($set)
    {
        $this->specs[] = $set;
    }

}