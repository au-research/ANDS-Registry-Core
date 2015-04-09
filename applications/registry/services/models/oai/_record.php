<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
define('SERVICES_MODULE_PATH', REGISTRY_APP_PATH.'services/');
class _record
{
	public $id;
	public $sets;
	private $header;
	private $_rec;
	public $mtime;

	/**
	 * @ignore
	 */
	public function __construct($registry_object, &$db)
	{
		$this->_rec = $registry_object;
		$this->id = $registry_object->registry_object_id;
		$this->mtime = $this->_latest($db);
	}

	public function is_deleted()
	{
		return strtolower($this->_rec->status) === "deleted";
	}

	public function is_collection()
	{
		return strtolower($this->_rec->class) === "collection";
	}


	public function header()
	{
		$this->header = array();
		$this->header['identifier'] = $this->identifier();
		$this->header['datestamp'] = $this->mtime;
		if (isset($this->sets))
		{
			if (is_array($this->sets))
			{
				$this->header['sets'] = $this->sets;
			}
			else
			{
				$this->header['sets'] = array($this->sets);
			}
		}
		return $this->header;
	}

	public function metadata($format, $nestlvl=0)
	{
		$lprefix = "";
		if ($nestlvl > 0)
		{
			foreach (range(0,$nestlvl) as $nest)
			{
				$lprefix .= "\t";
			}
		}
		$output = "";
		$data = false;
		switch($format)
		{
		case 'dci':
            require_once(REGISTRY_APP_PATH . '/services/method_handlers/dci.php');
            $dci_handler = new DCIMethod();
            $dci_handler->ro = $this->_rec;
            $dci_handler->populate_resource($this->id);
            $data = $dci_handler->ro_handle('dci');
			break;
		case 'oai_dc':
			$data = $this->_rec->transformToDC(false);
			break;
		case 'rif':
			$data = removeXMLDeclaration(wrapRegistryObjects($this->_rec->getRif()));
			break;
		case 'extRif':
			$data = removeXMLDeclaration($this->_rec->getExtRif());
			break;
        default:
            $data = removeXMLDeclaration($this->_rec->getRecordDataInScheme(null,$format));
		}
		if ($data)
		{
			foreach (explode("\n", $data) as $line)
			{
				if (empty($line))
				{
					continue;
				}
				$output .= $lprefix . $line . "\n";
			}
		}
		return $output;
	}

	/*
	 * Return an identifier template for this record. Needs to be passed through
	 * [s]printf, with the sole argument of the provider hostname. eg:
	 * `sprintf(_rec->identifier(), "ands.org.au");`
	 * @return an identifier template string
	 */
	public function identifier()
	{
		return sprintf("oai:%s::%d", "%s", $this->id);
	}

	/**
	 * Retrieve the latest timestamp for this record
	 * @param reference to CodeIgniter db object
	 * @return an ISO 8601 formatted string for the date
	 */
	private function _latest(&$db)
	{
		$created;
		$updated;
		//$deleted;

		foreach (array("created", "updated") as $type)
		{
			$query = $db->select_max("value")
				->get_where("registry_object_attributes",
					    array("registry_object_id" => $this->id,
						  "attribute" => $type));
			if ($query->num_rows() > 0)
			{
				$row = $query->result();
				$$type = $row[0]->value;
				if (!is_numeric($$type))
				{
					$$type = strtotime($$type);
				}
			}
		}

		return date('Y-m-d\TH:i:s\Z', max(array($created, $updated)));
	}

}

?>