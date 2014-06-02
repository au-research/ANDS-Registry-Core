<?php


class nlaPullback extends MX_Controller
{
	private $input; // pointer to the shell input
	private $start_time; // time script run (microtime float)
	private $exec_time; // time execution started
	private $_CI; 	// an internal reference to the CodeIgniter Engine 
	private $source;
	private $nlaPartyPrefix;  // all NLA party record identifiers start with this?
	private $nlaPartyDataSourceKey;  // what data source do we put them into?  
	private $nlaServiceURI;
	private $dataSource = null; // pointer to target DS. leave as null
	private $mode = '';

	function index()
	{
		$pullback_queue = array();

		/* Get a list of records which are related to NLA identifiers */
		echo "Fetching all records which are related to NLA identifiers or have an NLA party identifier..." .NL;
		$this->db->select("registry_object_id, related_object_key")->like("related_object_key", $this->nlaPartyPrefix, 'after')->from("registry_object_relationships");
		$query = $this->db->get();

		if (!$query->num_rows())
		{
			echo "Found no matching records...aborting." .NL;
			return;
		}

		/* Check that they are parties and queue up the list of NLA identifiers */
		foreach ($query->result_array() AS $result)
		{
			//$this->load->model('registry_object/registry_objects', 'ro');
			//$ro = $this->ro->getByID($result['registry_object_id']);
			//if ($ro) // no class checking!
			//{
				$pullback_queue[$result['related_object_key']] = $result['related_object_key'];
			//}
		}



		/* Get a list of records with NLA identifiers, excluding those already from our Party DS */
		$this->load->library('solr');
		$this->solr->setOpt('q', 'identifier_value:"'.$this->nlaPartyPrefix.'*" -data_source_id:'.$this->dataSource->id);
		$this->solr->setOpt('fl','identifier_value');
		$this->solr->setOpt('rows',1000);		
		$this->solr->executeSearch();
		if ($this->solr->getNumFound() > 0)
		{
			$result = $this->solr->getResult();
			foreach($result->docs AS $doc)
			{
				foreach($doc->identifier_value AS $identifier)
				{
					if (strpos($identifier, $this->nlaPartyPrefix) === 0) 
					{
						$pullback_queue[$identifier] = $identifier;
					}
				}

			}
		}

		echo "Queued " . count($pullback_queue) . " record(s) for pullback from NLA" . NL;

		$xml_fragments = $this->extractRIFCSfromQueue($pullback_queue);

		if ($xml_fragments)
		{
			echo "Retrieved " . count($xml_fragments) . " XML record fragment(s)..." . NL;

			$this->importer->setXML($xml_fragments);
			$this->importer->setDatasource($this->dataSource);
			$this->importer->forcePublish();
			$this->importer->commit();

			if ($this->importer->getErrors())
			{
				echo $this->importer->getErrors();
			}

			echo $this->importer->getMessages();
			
		}
		else
		{
			echo "No Registry Object XML to import. Exiting..." . NL;
			return;
		}

	}

    function extractRIFCSfromQueue($pullback_queue)
    {
        $fragments = '';
        if (is_array($pullback_queue))
        {
            foreach ($pullback_queue AS $identifier)
            {
                $fragment = $this->pullbackRIFCSfromNLA(trim($identifier));
                if ($fragment)
                {
                    $fragments .= unWrapRegistryObjects($fragment);
                }
            }
            $fragments=wrapRegistryObjects($fragments);
        }
        return $fragments;
    }

	function pullbackRIFCSfromNLA($identifier)
	{
		$target_uri = $this->nlaServiceURI . "?query=rec.identifier=%22" . $identifier . "%22&version=1.1&operation=searchRetrieve&recordSchema=http%3A%2F%2Fands.org.au%2Fstandards%2Frif-cs%2FregistryObjects";
		
		$response = curl_file_get_contents($target_uri);

		if ($response)
		{
			try
			{
				$sxml = @simplexml_load_string($response);
				if (!$sxml) { throw new Exception("No valid data! " . $target_uri); }
				$sxml->registerXPathNamespace("srw", "http://www.loc.gov/zing/srw/");
				$count = $sxml->xpath("//srw:searchRetrieveResponse/srw:numberOfRecords");
				if (is_array($count))
				{
					// Get the matching element
					$count = array_pop($count);
					$count = (int) $count;
				}

				if ($count)
				{
					$data = $sxml->xpath("//srw:recordData");
					if (is_array($data))
					{
						// Get the matching element
						$data = array_pop($data);
						if (is_object($data) && $data->registryObjects && !empty($data->registryObjects))
						{
							return $data->registryObjects->asXML();
						}
						else
						{
							echo "No registryObjects elements discovered inside SRW response: " . $identifier . NL;
						}
					}
				}
				else
				{
					echo "No matches from NLA SRU service on retrieving records for: " . $identifier . NL;
				}
			}
			catch (Exception $e)
			{
				echo "Unable to load XML from NLA endpoint for ".$identifier.". Response: " . $e->getMessage() . NL;
			}
		}
		ob_flush();flush();
		return;
	}



	function __construct()
    {
        parent::__construct();
        ob_start();
        $this->input = fopen ("php://stdin","r");
        $this->start_time = microtime(true);
		$this->_CI =& get_instance();
  	 	set_error_handler(array($this, 'cli_error_handler'));
  	 	set_exception_handler(array($this, 'cli_exception_handler'));
        define('IS_CLI_SCRIPT', true);



        /* Load config */
        $this->load->config('nla_pullback');

		parse_str(substr(strrchr($_SERVER['REQUEST_URI'], "?"), 1), $_GET);
		if(isset($_GET['mode']))
		{
 			$this->mode=$_GET['mode'];
		}

		$this->nlaPartyPrefix = $this->config->item('nlaPartyPrefix'); 
		$this->nlaPartyDataSourceKey = $this->config->item('nlaPartyDataSourceKey'.$this->mode); 
		$this->nlaServiceURI = $this->config->item('nlaServiceURI'.$this->mode);



		if (!$this->nlaPartyDataSourceKey)
		{
			echo "Not configured for NLA pullback - check your config options. Aborting..." .NL;
			return;
		}

		/* Check the target datasource */
		$this->_CI->load->model('data_source/data_sources', 'ds');
		$this->dataSource = $this->_CI->ds->getByKey($this->nlaPartyDataSourceKey);
		if (!$this->dataSource)
		{
			$this->dataSource = $this->ds->create($this->nlaPartyDataSourceKey, $this->config->item('nlaPartyDataSourceDefaultTitle'.$this->mode));
			$this->dataSource->setAttribute('title', $this->config->item('nlaPartyDataSourceDefaultTitle'.$this->mode));
			$this->dataSource->setAttribute('record_owner', 'SYSTEM');
			$this->dataSource->save();
			$this->dataSource->updateStats();
			echo "ERROR: Unable to match key for target NLA Pullback data source. Creating a new one..." .NL;
		}
    }

    function __destruct() 
    {
       print NL . NL . "Execution finished! Took " . sprintf ("%.3f", (float) (microtime(true) - $this->start_time)) . "s" . NL;
       $this->dataSource->append_log("Performing pullback of ".$this->mode." NLA records..." . NL . ob_get_contents());
       ob_end_flush();
   	}


   	private function getInput()
	{
		if (is_resource(($this->input)))
		{
			return trim(fgets($this->input));
		}
	}


	function cli_error_handler($number, $message, $file, $line, $vars)
	{
		if ($number == E_STRICT) { return true; }
		echo NL.NL.str_repeat("=", 15);
     	echo NL .NL . "An error ($number) occurred on line $line in the file: $file:" . NL;
        echo $message . NL;
        echo str_repeat("=", 15) . NL;

       //"<pre>" . print_r($vars, 1) . "</pre>";

        // Make sure that you decide how to respond to errors (on the user's side)
        // Either echo an error message, or kill the entire project. Up to you...
        // The code below ensures that we only "die" if the error was more than
        // just a NOTICE.
        if ( ($number !== E_NOTICE) && ($number < 2048) ) {
          //  die("Exiting on error...");
        }

	}

	function cli_exception_handler ($e)
	{
	  echo "Uncaught exception: " , $e->getMessage(), "\n";
	  echo $e->getTraceAsString();
	}
}