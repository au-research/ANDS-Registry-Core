<?php


namespace ANDS\Registry\Providers\Quality\Types;


use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;

abstract class CheckType
{
    // constants for metadata reporting
    public static $PASS = 'pass';
    public static $FAIL = 'fail';

    protected $msg = '';
    protected $name = 'check';

    /** @var RegistryObject */
    protected $record;

    /** @var \SimpleXMLElement */
    protected $simpleXML;

    /** @var boolean */
    private $result;

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
            'msg' => $this->msg,
            'name' => $this->name,
            'status' => $this->result ? static::$PASS : static::$FAIL
        ];
    }

    /**
     * @param $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }
}