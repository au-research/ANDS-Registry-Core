<?php
/**
 * Crosswalk to transform records from an NHMRC-provided
 * .csv data file into RIFCS with activity and party records. 
 * 
 * Business rules were prepared by Amir Aryani (ANDS) in
 * Crosswalk-NHMRC-RIF-CS-1-4.doc. 
 *
 * All activies will be created with keys in the 
 * purl.org/au-research/grants/nhmrc/<grant ID>
 *
 * Associated parties will have random keys and will be linked 
 * to the appropriate collections by related object. 
 *
 *
 * @author u4187959
 * @created 31/01/2013
 */

class ARC_to_RIFCS extends Crosswalk
{
    static $GRANT_ADMIN_LOOKUP = array("Commonwealth Scientific and Industrial Research Organisation (CSIRO)" =>  "http://nla.gov.au/nla.party-458251",
                                        "Australian Institute of Marine Science (AIMS)" => "http://nla.gov.au/nla.party-479444",
                                        "Australian Antarctic Division (AAD)" => "Contributor:Australian Antarctic Data Centre",
                                        "Burnet Institute (RO)" => "http://nla.gov.au/nla.party-1477251");
    static $SUBJECTS = array();

    const ARC_GROUP = 'Australian Research Council';
    const ARC_ORIGINATING_SOURCE = 'http://www.arc.gov.au/general/searchable_data.htm';
    const ARC_PREFIX = 'http://purl.org/au-research/grants/arc/';
    const ARC_NLA_KEY = 'http://nla.gov.au/nla.party-536838';

    private $parsed_array = array();
    private $csv_headings = array();
    private $people = array();
    private $grants = array();

	/**
	 * Identify this crosswalk (give a user-friendly name)
	 */
	public function identify()
	{
		return "ARC Grants CSV Data to RIFCS";
	}

    /**
     * Internal name for this metadataFormat
     */
    public function metadataFormat()
    {
        return "arc_csv";
    }

    /**
     * Do the transformation of the payload to RIFCS.
     *
     * Resultant RIFCS includes the native row (and headers) 
     * in a relatedInfo[type='nativeFormat'] element (which is
     * transformed away during the ingest process).
     *
     * @return string A valid RIFCS XML string (including wrapper)
     */
    public function payloadToRIFCS($payload, &$log = array())
    {
        // At least *try* and care about our memory management...
        unset($payload);
        $log[] = "[CROSSWALK] Attempting to execute crosswalk " . $this->identify();
        $log[] = (count($this->parsed_array) - 1) . " rows in initial input data";

        // First line has the column headings
        $this->csv_headings = array_shift($this->parsed_array);
        $grants = &$this->grants;
        $people = &$this->people;
        $rowCounter = 0;
        // Loop through each row, create an instance in the grants/people arrays
    	while($csv_values = array_shift($this->parsed_array))
        {
            // Map the column headings to each field, to simplify lookup
            ++$rowCounter;
            $row = $this->mapCSVHeadings($csv_values);

            if (!isset($row['Project ID']) || sizeof($this->csv_headings) != sizeof($csv_values))
            {
                $log[] = "[CROSSWALK] Ignoring blank/invalid row @".$rowCounter."...  heading count".sizeof($this->csv_headings) ."rowcount".sizeof($csv_values);
                continue; //skip blank rows
            }

            // First time we are seeing this grant...
            if (!isset($grants[$row['Project ID']]))
            {
                $grants[$row['Project ID']] = $row;
            }    

        }
        $log[] = "[CROSSWALK] Setup mapping in-memory. " . count($this->grants) . " grants, " . count($this->people) . " people";

        $this->renderActivities($log);
                    
    	return $this->returnChunks();
    }



    private function renderActivities(&$log)
    {

       //echo "size of Grants: ". sizeof($this->grants);
        foreach ($this->grants AS $activity)
        {
            // If MAIN_FUNDING_GROUP == 'Infrastructure Support', then "program", else "project"
//var_dump($activity);
            $activity_type = 'project';
            if(isset($activity['Scheme']) && (strpos($activity['Scheme'],'CE') !== false || strpos($activity['Scheme'],'CoE') !== false || strpos($activity['Scheme'],'LE') !== false || strpos($activity['Scheme'],'RN') !== false))
                $activity_type =  "program";

            $primary_name = 'No NAME';
            if(isset($activity['Project Title']))
                $primary_name = $this->normalise_space($activity['Project Title']);
            
            $budget =  'NaN';
            if(isset($activity['Funding Award']))
                $budget = $activity['Funding Award'];

            $benefit = 'NOT DISPLAYED';
            if(isset($activity['National/Community Benefit']))
                $benefit = $activity['National/Community Benefit'];

            $commYear = 'Not Available';
            if(isset($activity['Commencement Year']))
                $commYear = $activity['Commencement Year'];

            $description = htmlentities("
                            {$benefit}

                            Total Grant Budget: \$AUD {$budget}

                            Commencement Year: {$commYear}
                            
            ");

            /***
            * START BUILDING THE REGISTRY OBJECT
            ***/
            // Default group: <registryObject group="National Health and Medical Research Council">
            $registryObject = '<registryObject group="'. self::ARC_GROUP .'">' . NL;

            // Create the purl key: http://purl.org/au-research/grants/nhmrc/1501
            $registryObject .=  '<key>' . self::ARC_PREFIX . $activity['Project ID']. '</key>' . NL;
            $registryObject .=  '<originatingSource>'.self::ARC_ORIGINATING_SOURCE.'</originatingSource>' . NL;

            // It's an activity, duh? See activity_type business logic above
            $registryObject .=  '<activity type="'.$activity_type.'">' . NL;

            // Identifier is the same purl as our key
            $registryObject .=      '<identifier type="purl">' . self::ARC_PREFIX . $activity['Project ID'] . '</identifier>';
            $registryObject .=      '<identifier type="arc">' . $activity['Project ID'] . '</identifier>';
                        
            // Only include the alternative name if it is different to the primary
            $registryObject .=      '<name type="primary"><namePart>'.$primary_name.'</namePart></name>' . NL;

            // The string created above
            $registryObject .=      '<description type="full">'.trim($description).'</description>' . NL;
        
            $registryObject .=      ($activity['Commencement Year'] ? 
                                        '<existenceDates>'.NL.'<startDate dateFormat="W3CDTF">'.$activity['Commencement Year'].'</startDate>'.NL.'</existenceDates>' . NL 
                                        : '');

            // Include our subjects
            if(isset($activity['Primary FoR/RFCD']) && trim($activity['Primary FoR/RFCD']) != '')
            {
                if($this->isVocabResolvable($activity['Primary FoR/RFCD'], 'anzsrc-for'))
                {
                    $registryObject .=  '<subject type="anzsrc-for">'.$activity['Primary FoR/RFCD'].'</subject>' . NL;
                }
                else{
                    $registryObject .=  '<subject type="rfcd">'.$activity['Primary FoR/RFCD Description'].'</subject>' . NL;
                }
            }
            // Relate to the NHMRC's NLA key
            $registryObject .=      '<relatedObject>' . NL;
            $registryObject .=         '<key>'. self::ARC_NLA_KEY . '</key>' . NL;
            $registryObject .=         '<relation type="isFundedBy" />' . NL;
            $registryObject .=      '</relatedObject>' . NL;

            if ( isset($activity['Administering Organisation']) && $admin_institution_key = $this->resolveAustralianResearchInstitution($activity['Administering Organisation']))
            {
                // Relate to the GRANT Admin's key
                $registryObject .=      '<relatedObject>' . NL;
                $registryObject .=         '<key>'. $admin_institution_key . '</key>' . NL;
                $registryObject .=         '<relation type="isManagedBy" />' . NL;
                $registryObject .=      '</relatedObject>' . NL;
            }
            // Include the native format
            $registryObject .=      '<relatedInfo type="'.NATIVE_HARVEST_FORMAT_TYPE.'">' . NL;
            $registryObject .=          '<identifier type="internal">'.$this->metadataFormat().'</identifier>' . NL;
            $registryObject .=          '<notes><![CDATA[' . NL;   
            // Create the native format (csv) with prepended the column headings, up to DW_INDIVIDUAL_ID
          
            $native_values = $activity;
            foreach($native_values AS $key => &$val)
            {
                if (is_array($val)) { foreach ($val AS $subkey => $value) { if (!is_integer($subkey)) { $native_values[$subkey] = $value; } } unset($native_values[$key]); }
            }

            $registryObject .=              $this->wrapNativeFormat(array($this->csv_headings, $native_values)) . NL;
            $registryObject .=          ']]></notes>' . NL;
            $registryObject .=      '</relatedInfo>' . NL;

            $registryObject .=    '</activity>' . NL;
            $registryObject .='</registryObject>' . NL;

            $this->addRegistryObjectToChunkQueue($registryObject);
        }
    }

    private function isVocabResolvable($value, $type)
    {
        
        $CI =& get_instance();
        $CI->load->library('vocab');
     
        if(!isset(ARC_to_RIFCS::$SUBJECTS[$value]))
        {
            $resolvedValue = $CI->vocab->resolveSubject($value, $type);
            ARC_to_RIFCS::$SUBJECTS[$value] = true;
            if($resolvedValue['about'] == '')
            {
                ARC_to_RIFCS::$SUBJECTS[$value] = false;
            }
        }
        return ARC_to_RIFCS::$SUBJECTS[$value];

    }

    /**
     * 
     */
    private function resolveAustralianResearchInstitution($name)
    {
        // Don't make the same request twice
        if (isset(ARC_to_RIFCS::$GRANT_ADMIN_LOOKUP[$name]))
        {
            return ARC_to_RIFCS::$GRANT_ADMIN_LOOKUP[$name];
        }

        $CI =& get_instance();

        $solr_query = '+group:("Australian Research Institutions") AND +display_title:("'.trim($name).'")';
        $CI->load->library('solr');
        $CI->solr->setOpt('q', $solr_query);
        $result = $CI->solr->executeSearch(true);

        if (isset($result['response']['numFound']) && $result['response']['numFound'] > 0)
        {
            ARC_to_RIFCS::$GRANT_ADMIN_LOOKUP[$name] = $result['response']['docs'][0]['key'];
            return $result['response']['docs'][0]['key'];
        }
        elseif(strpos(trim($name),'The') === 0)
        {           
            $newName = substr(trim($name),4);
            $solr_query = '+group:("Australian Research Institutions") AND +display_title:("'.trim($newName).'")';
            $CI->load->library('solr');
            $CI->solr->setOpt('q', $solr_query);
            $result = $CI->solr->executeSearch(true);

            if (isset($result['response']['numFound']) && $result['response']['numFound'] > 0)
            {
                ARC_to_RIFCS::$GRANT_ADMIN_LOOKUP[$name] = $result['response']['docs'][0]['key'];
                return $result['response']['docs'][0]['key'];
            }        
        }
        else
        {
            return null;
        }

    }


    /**
     * Map the column headings to csv fields, creating an
     * associative array.
     *
     *
     * @return array (associative heading=>value)
     */
    private function mapCSVHeadings(array $csv_values)
    {
        $mapped_values = array();

        foreach($csv_values AS $idx => $csv_value)
        {
            $csv_value = htmlentities($csv_value);
            $heading = (isset($this->csv_headings[$idx]) ? $this->csv_headings[$idx] : 'NO_HEADING');
            $mapped_values[$heading] = $csv_value;
        }
        return $mapped_values;
    }

    /**
     * Emulate XSLT normalise-space() function.
     */
    private function normalise_space($string)
    {
        return trim(preg_replace("/\s+/", " ", $string));
    }


    /**
     * Wrap this format by simply including the header line and converting to CSV
     */
    public function wrapNativeFormat($payload)
    {
        $response = '';
        if (is_array($payload))
        {
            foreach($payload AS $row)
            {
                $response .= $this->sputcsv($row);
            }
        }
        else
        {
            return html_entity_decode($payload);
        }

        return trim($response);
    }


    var $registryObjectChunks = array();
    const CHUNK_SIZE = 10;

    function addRegistryObjectToChunkQueue($rifcs_xml)
    {
        $current_chunk = end($this->registryObjectChunks);
        if (!$current_chunk || count($current_chunk) == self::CHUNK_SIZE)
        {
            $this->registryObjectChunks[] = array($rifcs_xml);
        }
        else
        {
            $this->registryObjectChunks[count($this->registryObjectChunks)-1][] = $rifcs_xml;
        }
    }

    function returnChunks()
    {
        $ro_xml = '';
        foreach ($this->registryObjectChunks AS $chunk)
        {
            $ro_xml .= implode(NL, $chunk);
        }
        return wrapRegistryObjects($ro_xml);
    }

    /**
     * Write out an array to a STRING .csv (from http://php.net/manual/en/function.fputcsv.php)
     */
    private function sputcsv($row, $delimiter = ',', $enclosure = '"', $eol = "\n")
    {
        static $fp = false;
        if ($fp === false)
        {
            $fp = fopen('php://temp', 'r+'); // see http://php.net/manual/en/wrappers.php.php - yes there are 2 '.php's on the end.
            // NB: anything you read/write to/from 'php://temp' is specific to this filehandle
        }
        else
        {
            rewind($fp);
        }

        if (fputcsv($fp, $row, $delimiter, $enclosure) === false)
        {
            return false;
        }

        rewind($fp);
        $csv = fgets($fp);

        if ($eol != PHP_EOL)
        {
            $csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
        }

        return $csv;
    }


    /**
     * Validate that this payload is valid CSV
     * Note: doesn't check that the fields exist/are correct
     */
    public function validate($payload)
    {
        // Mac-PHP line endings bugfix:
        ini_set('auto_detect_line_endings',true);

        $valid = true; 

        // Memory hack...
        $payload = preg_split( '/\R+/', trim($payload) );
        $ref =& $payload;
        // Bizarrely, PHP doesn't support multiline in getcsv :-/
        foreach($ref AS $idx => $line)
        {
            $csv = str_getcsv($line, "|", '"', '\\');
            if (count($csv) > 0)
            {
                foreach($csv AS &$val)
                {
                    $val = str_replace('\\"','"', $val);
                }
                $this->parsed_array[] = $csv;
            }
        }
        if (count($this->parsed_array) == 0)
        {
            $valid = false;
        }

        unset($ref);
        unset($payload);
        gc_collect_cycles();

        return $valid;
    }

}
