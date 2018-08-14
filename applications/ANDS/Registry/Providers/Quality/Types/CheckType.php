<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

abstract class CheckType
{
    // constants for metadata reporting
    public static $PASS = 'pass';
    public static $FAIL = 'fail';

    /** @var RegistryObject */
    protected $record;

    /** @var \SimpleXMLElement */
    protected $simpleXML;

    /** @var boolean */
    private $result;

    /** @var array */
    protected $descriptor = [];

    /**
     * CheckType constructor.
     * @param RegistryObject $record
     * @param \SimpleXMLElement|null $simpleXML
     * @throws \Exception
     */
    public function __construct(RegistryObject $record, \SimpleXMLElement $simpleXML = null)
    {
        $this->record = $record;
        $this->simpleXML = $simpleXML ?: XMLUtil::getSimpleXMLFromString($record->getData()->data);
    }

    /**
     * Returns the status of the check
     *
     * @return boolean
     */
    abstract public function check();

    /**
     * @return array
     */
    public function toArray() {
        $this->result = $this->check();

        return [
            'name' => get_class($this),
            'status' => $this->result ? static::$PASS : static::$FAIL,
            'descriptor' => $this->descriptor($this->record->class)
        ];
    }

    public function descriptor($class)
    {
        if (!$this->descriptor) {
            return '';
        }

        return array_key_exists($class, $this->descriptor) ? $this->descriptor[$class] : '';
    }
}