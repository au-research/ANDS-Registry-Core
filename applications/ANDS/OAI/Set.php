<?php


namespace ANDS\OAI;


class Set
{
    private $setSpec = "";
    private $setName = "";

    /**
     * Set constructor.
     * @param string $setSpec
     * @param string $setName
     */
    public function __construct($setSpec, $setName)
    {
        $this->setSpec = $setSpec;
        $this->setName = $setName;
    }

    public function toArray()
    {
        return [
            'setSpec' => $this->setSpec,
            'setName' => $this->setName
        ];
    }

    /**
     * @return string
     */
    public function getSetSpec()
    {
        return $this->setSpec;
    }
}