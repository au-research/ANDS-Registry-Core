<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * OAI Provider controller
 *
 *
 * @author Steven McPhillips <steven.mcphillips@gmail.com>
 * @package ands/services/oai
 *
 */
class Oai extends MX_Controller
{

	const PROT_VER = "2.0";
	const ADMIN_EMAIL = "services@ands.org.au";
	const REP_NAME = "Australian National Data Service (ANDS)";
	const ERROR  = 'badArgument'; // generic error code for unexpected exceptions

	const LIST_I = 1; //resumption token source for ListIdentifiers
	const LIST_R = 2; //resumption token source for ListRecords
	const LIST_S = 3; //resumption token source for ListSets FIXME: currently unused

	private $responseDate; //timestamp for the response: used for the header, and resumable commands


	/*
	 * operations we can perform; anything else will result in an
	 * `Oai::BAD_VERB` error.
	 * hash value is an array that says :
	 *    - whether or not the operation is resumeable
	 *    - which function to call to handle the request
	 */
	private $verbs = array('GetRecord'           => array('handler' => 'get_record'),
			       'Identify'            => array('handler' => 'identify'),
			       'ListMetadataFormats' => array('handler' => 'list_formats'),
			       'ListIdentifiers'     => array('resume' => Oai::LIST_I,
							      'handler' => 'list_identifiers'),
			       'ListRecords'         => array('resume' => Oai::LIST_R,
							      'handler' => 'list_records'),
			       'ListSets'            => array('handler' => 'list_sets'));

	/**
	 * Array of formats this provider can provide. Currently DC and RIF
	 */
	private $formats = array(
		array('prefix' => 'dci',
		      'schema' => '',
		      'ns'     => ''),
		array('prefix' => 'oai_dc',
		      'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
		      'ns'     => 'http://www.openarchives.org/OAI/2.0/oai_dc/'),
		array('prefix' =>  RIFCS_SCHEME,
		      'schema' => 'http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd',
		      'ns'     => 'http://ands.org.au/standards/rif-cs/registryObjects'),
		array('prefix' => 'extRif',
		      'schema' => '',
		      'ns'     => 'http://ands.org.au/standards/rif-cs/extendedRegistryObjects'));

	/**
	 * nothing special; initialise the session and that's about it
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('xml');
		$this->load->helper('url');
		$this->base_url = base_url();
		date_default_timezone_set('UTC');
	}

	/**
	 * OAI doesn't really support nicely routed URLs, so everything is going
	 * to happen via this toplevel route, and get farmed off accordingly
	 */
	public function index()
	{
		$this->responseDate = gmmktime();
		$token = false;
		$this->_header();

		$verb = $this->input->get_post('verb', TRUE);
		if (!empty($verb) and
		    array_key_exists($verb, $this->verbs))
		{
			try
			{
				#is this operation resumable?
				if (array_key_exists('resume', $this->verbs[$verb]))
				{
					$token = $this->_check_resume(true,
								      $this->verbs[$verb]['resume']);
				}
				else
				{
					#if it isn't, check we haven't been sent one
					$this->_check_resume(false);
				}
			}
			catch (OAI_Exceptions $e)
			{
				$this->_do_error($e->oai_code(),
						 $e->getMessage());
				return;
			}
			catch (Exception $ee)
			{
				$this->_do_error(Oai::ERROR,
						 $e->getMessage());
				return;
			}

			try
			{
				$handler = $this->verbs[$verb]['handler'];
				call_user_func(array(&$this, $handler), $token);
			}
			catch (Oai_Exceptions $oai_error)
			{
				$this->_do_error($oai_error->oai_code(),
						 $oai_error->getMessage());
				return;
			}
			catch (Exception $error)
			{
				$this->_do_error(Oai::ERROR, $error->getMessage());
				return;
			}
		}
		else
		{
			$e = new Oai_BadVerb_Exceptions();
			$this->_do_error($e->oai_code(), $e->getMessage());
			return;
		}

		$this->_footer();
	}


	/**
	 * Oai::Identify handler
	 * This is fairly static content; farmed out to a static view
	 */
	public function identify($token=false)
	{
		$this->load->model('oai/records', 'records');
		$start = $this->records->earliest();
		$ident_details = array('repositoryName' => Oai::REP_NAME,
				       'baseUrl' => base_url(),
				       'protocolVersion' => Oai::PROT_VER,
				       'earliestTimestamp' => $start,
				       'deletedRecord' => 'transient', #persistent?
				       'granularity' => 'YYYY-MM-DDThh:mm:ssZ',
				       'adminEmail' => Oai::ADMIN_EMAIL);
		$this->output->append_output($this->load->view('oai/identify',
							       $ident_details,
							       true));
	}


	/**
	 * Oai::ListMetadataFormats handler
	 */
	public function list_formats($token=false)
	{
		$recs = array();
		$this->load->model('oai/Records', 'records');
		$ident = $this->input->get_post("identifier");
		if ($ident)
		{
			try
			{
				$this->records->identify($ident);
			}
			catch (OAI_Exceptions $e)
			{
				$this->_do_error($e->oai_code(), $e->getMessage());
				return;
			}
			catch (Exception $ee)
			{
				$this->_do_error(Oai::ERROR, $ee->getMessage());
				return;
			}
		}

		$this->output->append_output("\t<ListMetadataFormats>\n");
		foreach ($this->formats as $format)
		{
			$output = sprintf("\t\t<metadataFormat>\n" .
					  "\t\t\t<metadataPrefix>%s</metadataPrefix>\n" .
					  "\t\t\t<schema>%s</schema>\n" .
					  "\t\t\t<metadataNamespace>%s</metadataNamespace>\n" .
					  "\t\t</metadataFormat>",
					  $format['prefix'],
					  $format['schema'],
					  $format['ns']);
			$this->output->append_output($output);
		}
		$this->output->append_output("\t</ListMetadataFormats>\n");
	}

	/**
	 * Oai::ListIdentifiers handler
	 *
	 * we're meant to look at the metadataPrefix argument, but seeing as this service
	 * can provide all records in all formats, it's a bit moot. Check for its
	 * presence anyway, and throw a badArgument if its not there
	 *
	 * @param a resumption token (optional)
	 * @throw Oai_BadArgument_Exceptions if no metadataPrefix was specified
	 * @throw Oai_BadFormat_Exceptions if an invalid metadataPrefix was specified
	 */
	public function list_identifiers($token=false)
	{
		$details = $this->_do_list_resumable(OAI::LIST_I, $token);
		$response = $details['response'];
		$newtoken = $details['token'];
		if ($response['count'] > 0)
		{
			$this->output->append_output("\t<ListIndentifiers>\n");
			foreach ($response['records'] as $rec)
			{
				$header = $rec->header();
				$status = $rec->is_deleted() ? " status='deleted'" : "";
				$this->output->append_output(sprintf("\t\t<header%s>\n", $status));
				$this->output->append_output("\t\t\t<identifier>" .
							     sprintf($header['identifier'],
								     "ands.org.au") .
							     "</identifier>\n");
				$this->output->append_output("\t\t\t<datestamp>" .
							     $header['datestamp'] .
							     "</datestamp>\n");
				if (array_key_exists('sets', $header))
				{
					foreach ($header['sets'] as $set)
					{
						$this->output->append_output("\t\t\t" .
									     $set->asRef() .
									     "\n");
					}
				}
				$this->output->append_output("\t\t</header>\n");
			}
			$this->_inject_token($newtoken, $response['count'], $response['cursor']);
			$this->output->append_output("\t</ListIndentifiers>\n");
		}
	}

	/**
	 * Oai::ListSets handler
	 */
	public function list_sets($token=false)
	{
		$this->load->model('oai/Sets', 'sets');
		$this->output->append_output("\t<ListSets>\n");
		foreach ($this->sets->get() as $set)
		{
			$this->output->append_output("\t\t" . (string)$set . "\n");
		}
		$this->output->append_output("\t</ListSets>\n");
	}


	/**
	 * Oai::ListRecords handler
	 *
	 * see `list_identifiers`. (The logic is the same: the only difference
	 * being ListRecords returns full record metatada, not a record header)
	 *
	 * @param a resumption token (optional)
	 * @throw Oai_BadArgument_Exceptions if no metadataPrefix was specified
	 * @throw Oai_BadFormat_Exceptions if an invalid metadataPrefix was specified
	 */
	public function list_records($token=false)
	{
		$details = $this->_do_list_resumable(OAI::LIST_R, $token);
		$response = $details['response'];
		$newtoken = $details['token'];
		$format = $details['format'];
		if ($response['count'] > 0)
		{
			$this->output->append_output("\t<ListRecords>\n");
			foreach ($response['records'] as $rec)
			{
				
				$status = "";
				$deleted = false;
				if (isset($rec->status) &&  $rec->status == 'deleted')
				{
					$status = " status='deleted'";
					$deleted = true;
				}
				else{
					$header = $rec->header();
				}
				
				if($deleted)
				{
					$this->output->append_output("\t\t<record>\n");
					$this->output->append_output(sprintf("\t\t\t<header%s>\n", $status));
					$this->output->append_output("\t\t\t\t<identifier>" . sprintf($rec->registry_object_id, "ands.org.au") . "</identifier>\n");
					$this->output->append_output("\t\t\t\t<datestamp>" .gmdate('Y-m-d\TH:i:s\+\Z', $rec->deleted) ."</datestamp>\n");
					foreach ($rec->sets as $key=>$val)
					{
						$this->output->append_output(sprintf("\t\t\t\t<setSpec>%s:%s</setSpec>\n", $key, $val));
					}
					$this->output->append_output("\t\t\t</header>\n");
					$this->output->append_output("\t\t</record>\n");

				}
				elseif($format == 'dci')
				{
                    $dciDoc = "";
                    if($rec->is_collection())
                    {
                        try
                        {
                            $dciDoc = $rec->metadata($format,3);
                        }
                        catch (Exception $e)
                        {/*eek... would be good to log these...*/}
                    }
                    if($dciDoc){
                        $this->output->append_output("\t\t<record>\n");
                        $this->output->append_output(sprintf("\t\t\t<header%s>\n", $status));
                        $this->output->append_output("\t\t\t\t<identifier>" .
                                         sprintf($header['identifier'],
                                             "ands.org.au") .
                                         "</identifier>\n");
                        $this->output->append_output("\t\t\t\t<datestamp>" .
                                         $header['datestamp'] .
                                         "</datestamp>\n");
                        if (array_key_exists('sets', $header))
                        {
                            foreach ($header['sets'] as $set)
                            {
                                $this->output->append_output("\t\t\t\t" .
                                                 $set->asRef() .
                                                 "\n");
                            }
                        }
                        $this->output->append_output("\t\t\t</header>\n");
                        $this->output->append_output("\t\t\t<metadata>\n");
                        $this->output->append_output($dciDoc);
                        $this->output->append_output("\t\t\t</metadata>\n");
                        $this->output->append_output("\t\t</record>\n");
                    }
				}
                else
                {
                    $this->output->append_output("\t\t<record>\n");
                    $this->output->append_output(sprintf("\t\t\t<header%s>\n", $status));
                    $this->output->append_output("\t\t\t\t<identifier>" .
                        sprintf($header['identifier'],
                            "ands.org.au") .
                        "</identifier>\n");
                    $this->output->append_output("\t\t\t\t<datestamp>" .
                        $header['datestamp'] .
                        "</datestamp>\n");
                    if (array_key_exists('sets', $header))
                    {
                        foreach ($header['sets'] as $set)
                        {
                            $this->output->append_output("\t\t\t\t" .
                                $set->asRef() .
                                "\n");
                        }
                    }
                    $this->output->append_output("\t\t\t</header>\n");
                    $this->output->append_output("\t\t\t<metadata>\n");
                    try
                    {
                        $this->output->append_output( $rec->metadata($format,3));
                    }
                    catch (Exception $e) {/*eek... would be good to log these...*/}
                    $this->output->append_output("\t\t\t</metadata>\n");
                    $this->output->append_output("\t\t</record>\n");
                }
			}
			$this->_inject_token($newtoken, $response['count'], $response['cursor']);
			$this->output->append_output("\t</ListRecords>\n");
		}
		else 
		{
			$this->output->append_output("\t<ListRecords>\n");
			$this->_inject_token(chr(0), 0, 0);
			$this->output->append_output("\t</ListRecords>\n");
		}
	}

	public function get_record()
	{
		$this->load->model('oai/Records', 'records');
		$identifier = $this->input->get_post("identifier");
		$format= $this->input->get_post("metadataPrefix");

		if (!$identifier)
		{
			throw new Oai_BadArgument_Exceptions("Missing required argument 'identifier'");
		}
		if (!$format)
		{
			throw new Oai_BadArgument_Exceptions("Missing required argument 'metadataPrefix'");
		}

		$rec = $this->records->getByIdentifier($identifier);
		if ($rec)
		{
			$status = "";
			$deleted = false;
			$this->output->append_output("\t<GetRecord>\n");
			if (isset($rec->status) &&  $rec->status == 'deleted')
			{
				$status = " status='deleted'";
				$deleted = true;
			}
			else{
				$header = $rec->header();
			}
			
			if($deleted)
			{
				$this->output->append_output("\t\t<record>\n");
				$this->output->append_output(sprintf("\t\t\t<header%s>\n", $status));
				$this->output->append_output("\t\t\t\t<identifier>" . sprintf($rec->registry_object_id, "ands.org.au") . "</identifier>\n");
				$this->output->append_output("\t\t\t\t<datestamp>" .gmdate('Y-m-d\TH:i:s\+\Z', $rec->deleted) ."</datestamp>\n");
				foreach ($rec->sets as $key=>$val)
				{
					$this->output->append_output(sprintf("\t\t\t\t<setSpec>%s:%s</setSpec>\n", $key, $val));
				}
				$this->output->append_output("\t\t\t</header>\n");
				$this->output->append_output("\t\t</record>\n");

			}
			else
			{

				$this->output->append_output("\t\t<record>\n");
				$this->output->append_output(sprintf("\t\t\t<header%s>\n", $status));
				$this->output->append_output("\t\t\t\t<identifier>" .
							     sprintf($header['identifier'],
								     "ands.org.au") .
							     "</identifier>\n");
				$this->output->append_output("\t\t\t\t<datestamp>" .
							     $header['datestamp'] .
							     "</datestamp>\n");
				if (array_key_exists('sets', $header))
				{
					foreach ($header['sets'] as $set)
					{
						$this->output->append_output("\t\t\t\t" .
									     $set->asRef() .
									     "\n");
					}
				}
				$this->output->append_output("\t\t\t</header>\n");
				$this->output->append_output("\t\t\t<metadata>\n");
				try
				{
				    $this->output->append_output( $rec->metadata($format, 3));
				}
				catch (Exception $e) {/*eek... would be good to log these...*/}
				$this->output->append_output("\t\t\t</metadata>\n");
				$this->output->append_output("\t\t</record>\n");
				
			}
			$this->output->append_output("\t</GetRecord>\n");
		}
		else 
		{
			$this->output->append_output("\t<GetRecord>\n");
			$this->_inject_token(chr(0), 0, 0);
			$this->output->append_output("\t</GetRecord>\n");
		}
	}

	/*******
	 *
	 * handler helpers
	 *
	 *******/

	/**
	 * given an array of query parameters, extract those pertaining to the OAI protocol:
	 *  - verb
	 *  - identifier
	 *  - metadataPrefix
	 *  - from
	 *  - until
	 *  - set
	 *  - resumptionToken
	 *
	 * @param an array of HTTP query params (eg $_GET, $_POST, $_REQUEST)
	 * @return an array of OAI query params
	 */
	private function _oai_params($params)
	{
	    $whitelist = array ('verb' => '',
				'identifier' => '',
				'metadataPrefix' => '',
				'from' => '',
				'until' => '',
				'set' => '',
				'resumptionToken' => '');
	    return array_intersect_key($params, $whitelist);
	}

	/**
	 * @ignore
	 */
	private function _do_list_resumable($resume_type, $token=false)
	{
		$supplied_format = $from = $until = $from_set = $start = $set = $created = false;
		if ($token)
		{
			$supplied_format = $token['format'];
			$from = $token['from'];
			$until = $token['until'];
			$from_set = $token['set'];
			$start = $token['cursor'];
			$created = $token['created'];
		}
		else
		{
			$supplied_format = $this->input->get_post('metadataPrefix', TRUE);
			$from = $this->input->get_post('from', TRUE);
			$until = $this->input->get_post('until', TRUE);
			$from_set = $this->input->get_post('set', TRUE);
			$start = 0;
			$created = $this->responseDate;
		}
		if ($from)
		{
			$from = $this->_parse_datetime($from);
		}
		if ($until)
		{
			$until = $this->_parse_datetime($until);
		}

		if ($from_set)
		{
			$this->load->model('oai/Sets', 'sets');
			$set = $this->sets->getBySpec($from_set);
		}


		if (!$supplied_format)
		{
			throw new Oai_BadArgument_Exceptions("Missing required argument 'metadataPrefix'");
		}
		else
		{
			#do we support this prefix?
			$valid = false;
			foreach($this->formats as $format)
			{
				if ($format['prefix'] == $supplied_format)
				{
					$valid = true;
					break;
				}
			}
			if (!$valid)
			{
				throw new Oai_BadFormat_Exceptions($supplied_format);
			}
			$this->load->model('oai/Records', 'records');
			//getting records can throw an exception, so retrieve them before
			//appending any output
			$response = $this->records->get($set,
							$from,
							$until,
							$start);
			if ($from instanceof DateTime)
			{
				$from = $from->format('U');
			}
			if ($until instanceof DateTime)
			{
				$until = $until->format('U');
			}
			if ($response['cursor'] < $response['count'])
			{
				$newtoken = $this->token_for(array('source' => $resume_type,
								   'cursor' => $response['cursor'],
								   'created' => $created,
								   'format' => $supplied_format,
								   'from' => $from,
								   'until' => $until,
								   'set' => $from_set));
			}
			else
			{
				$newtoken = false;
			}
			return (array('token' => $newtoken,
				      'response' => $response,
				      'format' => $supplied_format));
		}
	}

	/**
	 * @ignore
	 */
	private function _inject_token($token, $count, $cursor)
	{
	    if ($token)
	    {
		$this->output->append_output(sprintf("\t\t<resumptionToken cursor='%d' completeListSize='%d'>%s</resumptionToken>\n",
						     $cursor,
						     $count,
						     $token));
	    }
	}

	/**
	 * create a resumption token (urlencoded, base64_encoded, serialised, packed array)
	 * @param an associative array of resumption data:
	 *  - source: what sort of command are we resuming (OAI::LIST_I | OAI::LIST_R | OAI::LIST_S)
	 *  - format: 'metadataPrefix' argument (str)
	 *  - cursor: number of records provided so far (int)
	 *  - created: when the first request was created (int, timestamp)
	 *  - from: 'from' argument, converted to timestamp (int, timestamp)
	 *  - until: 'until' argument, converted to timestamp (int, timestamp)
	 *  - set: 'set' argument (str)
	 * @return a valid resumptionToken. when unpacked, array structure is:
	 *  - [0] source: enum (OAI::LIST_I, OAI::LIST_R, OAI::LIST_S)
	 *  - [1] format: metadataFormat
	 *  - [2] cursor: int (pack 'I*')
	 *  - [3] created: timestamp  (seconds, pack 'I*')
	 *  - [4] from: timestamp (seconds, pack 'I*')
	 *  - [5] until: timestamp  (seconds, pack 'I*')
	 *  - [6] set: setSpec
	 */
	public function token_for($params)
	{
		$token = array();
		$token[] = $params['source'];
		$token[] = $params['format'];
		$token[] = pack('I*', $params['cursor']);
		$token[] = pack('I*', $params['created']);
		$token[] = pack('I*', $params['from']);
		$token[] = pack('I*', $params['until']);
		$token[] = $params['set'];
		return rawurlencode(base64_encode(serialize($token)));
	}

	/**
	 * parse a resumption token, returning the requisite
	 * pieces of data encoded with:
	 *  - from: timestamp
	 *  - until: timestamp
	 *  - created: timestamp
	 *  - set: setSpec
	 *  - format: metadataFormat
	 *  - cursor: int
	 *
	 * @param something passing itself off as a resumptionToken
	 * @param the source for which this resumption token is being
	 * processed for (enum: OAI::LIST_I, OAI::LIST_R)
	 * @param maximum age (in seconds) for which the token is
	 * valid (defaults to 20 minutes i.e. 1200)
	 * @return a hash of resumption data (see above for details)
	 * @throw OAI_BadToken_Exceptions if the token is invalid (either
	 * due to formatting, or expiration)
	 */
	public function parse_token($token, $for, $max_age=120000)
	{
		$resume = array();
		try
		{
			$data = rawurldecode($token);
			$data = base64_decode($data, true);
			$data = unserialize($data);
			if ($data !== false)
			{
				# was this token created for the same source as the
				# current command?
				if ($for == $data[0] and
				    ($data[0] == Oai::LIST_I or
				     $data[0] == Oai::LIST_R or
				     $data[0] == Oai::LIST_S))
				{
					$resume['source'] = $data[0];
					$resume['format'] = $data[1];
					$resume['set'] = $data[6];

					$unpack = unpack('I*', $data[2]);
					$resume['cursor'] = $unpack[1];
					$unpack = unpack('I*', $data[3]);
					$resume['created'] = $unpack[1];
					$unpack = unpack('I*', $data[4]);
					$resume['from'] = $unpack[1];
					$unpack = unpack('I*', $data[5]);
					$resume['until'] = $unpack[1];

					# now, some basic data validation

					# are our timestamps actual timestamps
					# (if they exist at all)?
					# c.f. http://stackoverflow.com/a/2524761/664095
					foreach (array($resume['created'],
						       $resume['from'],
						       $resume['until']) as $t)
					{
						$t = (int)$t;
						if ($t === null or is_numeric($t))
						{
							continue;
						}
						else
						{
							throw new Exception("unexpected format: malformed timestamp");
						}
					}

					# check if the token is still valid
					if ($this->responseDate > $resume['created'] + $max_age)
					{
						$diff = $this->responseDate - $resume['created'] + $max_age;
						throw new Exception("this token expired " . $diff . " seconds ago");
					}

					# if we've made it this far, the token
					# is usable
					return $resume;
				}
				else
				{
					throw new Exception("this token was created for another command");
				}

			}
		        else
			{
				throw new Exception("unexpected format: couldn't unmarshall token");
			}
		}
		catch (Exception $e)
		{
			throw new Oai_BadToken_Exceptions($e->getMessage());
		}
	}



	/**
	 * Parse incoming UTCdatetime parameters into a PHP DateTime
	 *
	 * Parameters are of the format: YYYY-MM-DD[Thh:mm:ssZ]
	 * (c.f. http://www.openarchives.org/OAI/openarchivesprotocol.html#Dates),
	 * or a UNIX timestamp (seconds from epoch; integer)
	 *
	 * @param an ISO8601 formatted UTCdatetime-stamp
	 * @return a PHP DateTime
	 * @throw an Oai_BadArgument_Exceptions if the parameter is formatted
	 * incorrectly
	 */
	private function _parse_datetime($dateish)
	{
		$date = false;

		if (is_numeric($dateish))
		{
			$date = DateTime::createFromFormat("U", $dateish);
		}
		else
		{
			$date = DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $dateish);
			if ($date == false)
			{
				//lets try without a timestamp
				$date = DateTime::createFromFormat("Y-m-d", $dateish);
			}
		}


		if ($date == false)
		{
			throw new Oai_BadArgument_Exceptions("timestamp '$dateish' is incorrectly formatted");
		}
		else
		{
			return $date;
		}
	}

	/**
	 * Check for the existence of a valid resumption token.
	 * @param whether or not a resumption token is allowed in this context.
	 * If this is false and a token is found, the token is deemed invalid
	 * (see 'throws' details below)
	 * @param referring command; one of Oai::LIST_I, Oai::LIST_R, Oai::LIST_S
	 * @return false if no token found, true if valid token found
	 * @throw UnexpectedValueException when other request params are found
	 * @throw InvalidArgumentException when the token is invalid, or expired
	 *
	 */
	private function _check_resume($allowed=true, $for=null)
	{
		$token = $this->input->get_post('resumptionToken', TRUE);
		if ($token and !$allowed)
		{
			throw new Oai_BadToken_Exceptions("token not permitted here");
		}
		elseif ($token and $allowed)
		{
			$resume = $this->parse_token($token, $for);
			if ($resume === false)
			{
				throw new Oai_BadToken_Exceptions();
			}

			if (count($this->_oai_params($_REQUEST)) > 2)
			{
				throw new Oai_BadArgument_Exceptions("'resumptionToken' is an exclusive parameter");
			}
			else
			{
				return $resume;
			}
		}
		else
		{
			return false;
		}
	}


	/*******
	 *
	 * layout helpers
	 *
	 *******/

	private function _do_error($code, $msg=false)
	{
		$package = sprintf("\t<error code=\"%s\">%s</error>",
				   $code,
				   $msg ? $msg : $this->err_codes[$code]);
		$this->output->append_output($package . "\n");
		$this->_footer();
	}

	private function _header()
	{
		$_header = <<<XMLHEAD
<?xml version="1.0" encoding="UTF-8"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	 xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">

XMLHEAD;
		#CI's time helper is crap for just getting a UTC timestamp; using POPHP)
		$response_date = gmdate('Y-m-d\TH:i:s\Z', $this->responseDate);
		$this->output->set_content_type('application/xml');
		$this->output->set_output(trim($_header));
		$this->output->append_output(sprintf("\t<responseDate>%s</responseDate>",
			$response_date) . "\n");

		$request = "\t<request";
		foreach ($this->_oai_params($_REQUEST) as $param=>$val)
		{
		$request .= sprintf(' %s="%s"', $param, $val);
	        }
		$request .= sprintf('>%s</request>', current_url());
		$this->output->append_output($request . "\n");
	}

	private function _footer()
	{
		$this->output->append_output("</OAI-PMH>");
	}

}

?>
