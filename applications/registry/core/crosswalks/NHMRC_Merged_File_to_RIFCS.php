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
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @created 31/01/2013
 */

class NHMRC_Merged_File_to_RIFCS extends Crosswalk
{
    static $GRANT_ADMIN_LOOKUP = array();

    const NHMRC_GROUP = 'National Health and Medical Research Council';
    const NHMRC_ORIGINATING_SOURCE = 'http://www.nhmrc.gov.au/';
    const NHMRC_GRANT_PREFIX = 'http://purl.org/au-research/grants/nhmrc/';
    const NHMRC_PARTY_PREFIX = 'http://nhmrc.gov.au/person/';
    const NHMRC_PROGRAM_TYPE_GROUP = 'Infrastructure Support';
    const NHMRC_NLA_KEY = 'http://nla.gov.au/nla.party-616216';
    const NHMRC_LOGO = 'http://services.ands.org.au/documentation/logos/nhmrc_stacked_small.jpg';
    const NHMRC_MIN_GRANT_YR = 2009;

    private $parsed_array = array();
    private $csv_headings = array();
    private $people = array();
    private $grants = array();

	/**
	 * Identify this crosswalk (give a user-friendly name)
	 */
	public function identify()
	{
		return "NHMRC Grants CSV Data to RIFCS";
	}

    /**
     * Internal name for this metadataFormat
     */
    public function metadataFormat()
    {
        return "nhmrc_csv";
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

        // Loop through each row, create an instance in the grants/people arrays
    	while($csv_values = array_shift($this->parsed_array))
        {
            // Map the column headings to each field, to simplify lookup
            $row = $this->mapCSVHeadings($csv_values);

            if (!isset($row['GRANT_ID']) || !isset($row['DW_INDIVIDUAL_ID']) || trim($row['GRANT_SIMPLIFIED_TITLE']) == '' || sizeof($this->csv_headings) != sizeof($csv_values))
            {
                $log[] = "[CROSSWALK] Ignoring blank/invalid row...";
                continue; //skip blank rows
            }

            // First time we are seeing this grant...
            if (!isset($grants[$row['GRANT_ID']]))
            {
                $grants[$row['GRANT_ID']] = $row;
                $grants[$row['GRANT_ID']]['people'] = array();
            }
            
            // First time we are seeing this person
            if (!isset($people[$row['DW_INDIVIDUAL_ID']]))
            {
                $people[$row['DW_INDIVIDUAL_ID']] = $row;
                $people[$row['DW_INDIVIDUAL_ID']]['grants'] = array();
            }

            // Link the person to the grant
            $grants[$row['GRANT_ID']]['people'][] = $row['DW_INDIVIDUAL_ID'];

            // Link the grant to the person
            if (!isset($people[$row['DW_INDIVIDUAL_ID']]['grants'][$row['GRANT_ROLE']]))
            {
                $people[$row['DW_INDIVIDUAL_ID']]['grants'][$row['GRANT_ROLE']] = array($row['GRANT_ID']);
            }
            else
            {
                $people[$row['DW_INDIVIDUAL_ID']]['grants'][$row['GRANT_ROLE']][] = $row['GRANT_ID'];
            }

        }
        $log[] = "[CROSSWALK] Setup mapping in-memory. " . count($this->grants) . " grants, " . count($this->people) . " people";

        $this->renderParties($log);
        $this->renderActivities($log);
        var_dump($log);

    	return $this->returnChunks();
    }



    private function renderActivities(&$log)
    {

        foreach ($this->grants AS $activity)
        {
            // If MAIN_FUNDING_GROUP == 'Infrastructure Support', then "program", else "project"
            $activity_type = ($activity['MAIN_FUNDING_GROUP'] == self::NHMRC_PROGRAM_TYPE_GROUP ? "program" : "project");

            $primary_name = $this->normalise_space($activity['GRANT_SIMPLIFIED_TITLE']);
            $alternative_name = $this->normalise_space($activity['GRANT_SCIENTIFIC_TITLE']);


            // Get the names of our investigators
            $investigators = array();
            foreach ($activity['people'] AS $person)
            {
                $name = implode(" ", array($this->people[$person]['TITLE'],$this->people[$person]['FIRST_NAME'],$this->people[$person]['LAST_NAME']));
                if ($name != $activity['CIA_NAME'])
                {
                    $investigators[] = $name;
                }
            }
            $investigators = implode("; ", $investigators);

            $formatted_media_summary = trim($activity['MEDIA_SUMMARY']);
            if ($formatted_media_summary) { $formatted_media_summary . "\n\n"; }

            $description = htmlentities("{$formatted_media_summary}

                            Lead Investigator: {$activity['CIA_NAME']}
                            " . ($investigators ? "
                            Co-Investigator(s): {$investigators}
                            " : "") . "
                            Total Grant Budget: \$AUD {$activity['TOTAL_GRANT_BUDGET']}

                            Application Year: {$activity['APPLICATION_YEAR']}
                            Start Year: {$activity['START_YR']}
                            End Year: {$activity['END_YR']}

                            Main Funding Group: {$activity['MAIN_FUNDING_GROUP']}
                            Grant Type (Funding Scheme): {$activity['HIGHER_GRANT_TYPE']}
                            Grant Sub Type: {$activity['SUB_TYPE']}
            ");

            /***
            * START BUILDING THE REGISTRY OBJECT
            ***/
            // Default group: <registryObject group="National Health and Medical Research Council">
            $registryObject = '<registryObject group="'. self::NHMRC_GROUP .'">' . NL;

            // Create the purl key: http://purl.org/au-research/grants/nhmrc/1501
            $registryObject .=  '<key>' . self::NHMRC_GRANT_PREFIX . $activity['GRANT_ID']. '</key>' . NL;
            $registryObject .=  '<originatingSource>'.self::NHMRC_ORIGINATING_SOURCE.'</originatingSource>' . NL;

            // It's an activity, duh? See activity_type business logic above
            $registryObject .=  '<activity type="'.$activity_type.'">' . NL;

            // Identifier is the same purl as our key
            $registryObject .=      '<identifier type="purl">' . self::NHMRC_GRANT_PREFIX . $activity['GRANT_ID'] . '</identifier>';
            $registryObject .=      '<identifier type="nhmrc">' . $activity['GRANT_ID'] . '</identifier>';
                        
            // Only include the alternative name if it is different to the primary
            $registryObject .=      '<name type="primary"><namePart>'.$primary_name.'</namePart></name>' . NL;
            if($primary_name != $alternative_name) {
            $registryObject .=      '<name type="alternative"><namePart>'.$alternative_name.'</namePart></name>' . NL;
            }

            // The string created above
            $registryObject .=      '<description type="notes">'.trim($description).'</description>' . NL;
            $registryObject .=      '<description type="logo">'.self::NHMRC_LOGO.'</description>' . NL;

            // Include our subjects
            $registryObject .=      ($activity['BROAD_RESEARCH_AREA'] ? 
                                        '<subject type="local">'.$activity['BROAD_RESEARCH_AREA'].'</subject>' . NL 
                                        : '');
            $registryObject .=      ($activity['FOR_CATEGORY'] ? 
                                        '<subject type="anzsrc-for">'.$activity['FOR_CATEGORY'].'</subject>' . NL
                                        : '');
            $registryObject .=      ($activity['FIELD_OF_RESEARCH'] ? 
                                        '<subject type="anzsrc-for">'.$activity['FIELD_OF_RESEARCH'].'</subject>' . NL
                                        : '');

            // And any keywords as local subjects
            foreach ($activity['keywords'] AS $kw)
            {
                if ($kw != '')
                {
                    $registryObject .= '<subject type="local">'.$kw.'</subject>' . NL;
                }
            }

            // Relate to the NHMRC's NLA key
            $registryObject .=      '<relatedObject>' . NL;
            $registryObject .=         '<key>'. self::NHMRC_NLA_KEY . '</key>' . NL;
            $registryObject .=         '<relation type="isFundedBy" />' . NL;
            $registryObject .=      '</relatedObject>' . NL;

            // Relate to key for GRANT_ADMIN_INSTITUTION
            if ($admin_institution_key = $this->resolveAustralianResearchInstitution($activity['GRANT_ADMIN_INSTITUTION']))
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



    private function renderParties(&$log)
    {
        foreach ($this->people AS $party)
        {
            // Only create party records for those we know are linked to post-2009 records
            $valid_to_create = false;
            foreach ($party['grants'] AS $grant_role => $_)
            {
                foreach ($party['grants'][$grant_role] AS $grant_id)
                {
                    if ($this->grants[$grant_id]['START_YR'] >= self::NHMRC_MIN_GRANT_YR)
                    {
                        $valid_to_create = true;
                    }
                }
            }

            // Skip if we're not valid to create
            if (!$valid_to_create) continue;


            // START BUILDING THE REGISTRY OBJECT
            // Default group: <registryObject group="National Health and Medical Research Council">
            $registryObject = '<registryObject group="'. self::NHMRC_GROUP .'">' . NL;

            // Create the purl key: http://purl.org/au-research/grants/nhmrc/1501
            $registryObject .=  '<key>' . self::NHMRC_PARTY_PREFIX . $party['DW_INDIVIDUAL_ID']. '</key>' . NL;
            $registryObject .=  '<originatingSource>'.self::NHMRC_ORIGINATING_SOURCE.'</originatingSource>' . NL;

            // It's an activity, duh? See activity_type business logic above
            $registryObject .=  '<party type="person">' . NL;

            // Only include the alternative name if it is different to the primary
            $registryObject .=      '<name type="primary">' . NL;

            if ($party['TITLE'])
                $registryObject .=          '<namePart type="title">'.$party['TITLE'].'</namePart>' . NL;

            $registryObject .=          '<namePart type="family">'.$party['LAST_NAME'].'</namePart>' . NL;
            $registryObject .=          '<namePart type="given">'.$party['FIRST_NAME'].'</namePart>' . NL;
            $registryObject .=      '</name>' . NL;

            //$registryObject .=      '<description type="logo">'.self::NHMRC_LOGO.'</description>' . NL;
            // Get rid of NHMRC logo for parties

            if ($party['grants'] && count($party['grants']) > 0)
            {

                $registryObject .=      '<description type="full">' . NL;
                $registryObject .=      'Participant in the following NHMRC Grant(s):' . NL;

                $description_elt =      '<ul>' . NL;
                foreach ($party['grants'] AS $grant_role => $_)
                {
                    foreach ($party['grants'][$grant_role] AS $grant_id)
                    {
                        if ($this->grants[$grant_id]['GRANT_SIMPLIFIED_TITLE'])
                        {
                            $description_elt  .= '<li>' . $this->normalise_space($this->grants[$grant_id]['GRANT_SIMPLIFIED_TITLE']) .  '</li>';
                        }
                    }
                }
                $description_elt  .=      '</ul>' . NL;

                $registryObject .= htmlentities($description_elt);
                $registryObject .=      '</description>' . NL;
            }

            // Relate to the grant key(s)
            foreach ($party['grants'] AS $grant_role => $grant_ids)
            {
                foreach($grant_ids AS $grant_id)
                {
                    $registryObject .=      '<relatedObject>' . NL;
                    $registryObject .=         '<key>'. self::NHMRC_GRANT_PREFIX . $grant_id . '</key>' . NL;
                    
                    if( $grant_role == "CIA" )
                    {
                        $registryObject .=         '<relation type="isPrincipalInvestigatorOf" />' . NL;
                    }
                    else
                    {
                        $registryObject .=         '<relation type="isParticipantIn" />' . NL;
                    }
                    $registryObject .=      '</relatedObject>' . NL;
                }
            }

             // Include the native format
            $registryObject .=      '<relatedInfo type="'.NATIVE_HARVEST_FORMAT_TYPE.'">' . NL;
            $registryObject .=          '<identifier type="internal">'.$this->metadataFormat().'</identifier>' . NL;
            $registryObject .=          '<notes><![CDATA[' . NL;   

                // Clean up the CSV contents
                // Get all the party values (those that come after DW-INDIVIDUAL_ID (excluding DW_IND_ID!))
                $native_values = array_slice($party, array_search('DW_INDIVIDUAL_ID', $party)+1);
                if(isset($native_values['grants'])) { unset($native_values['grants']); }
                $native_values['GRANT_ID'] = $party['GRANT_ID'];
                foreach($native_values AS $key => &$val)
                {
                    if (is_array($val)) { foreach ($val AS $subkey => $value) { $native_values[$subkey] = $value; } unset($native_values[$key]); }
                }
                // Hide the private DW_INDIVIDUAL_ID
                $native_values['HASHED_DW_INDIVIDUAL_ID'] = $party['DW_INDIVIDUAL_ID'];

            // Create the native format (csv) with prepended the column headings
            $registryObject .=              $this->wrapNativeFormat(array($this->csv_headings, $native_values)) . NL;
            $registryObject .=          ']]></notes>' . NL;
            $registryObject .=      '</relatedInfo>' . NL;
            $registryObject .=    '</party>' . NL;
            $registryObject .='</registryObject>' . NL;

            $this->addRegistryObjectToChunkQueue($registryObject);
        }
    }


    /**
     * 
     */
    private function resolveAustralianResearchInstitution($name)
    {
        // Don't make the same request twice
        if (isset(NHMRC_Merged_File_to_RIFCS::$GRANT_ADMIN_LOOKUP[$name]))
        {
            return NHMRC_Merged_File_to_RIFCS::$GRANT_ADMIN_LOOKUP[$name];
        }

        $CI =& get_instance();

        $solr_query = '+group:("Australian Research Institutions") AND +display_title:("'.$name.'")';
        $CI->load->library('solr');
        $CI->solr->setOpt('q', $solr_query);
        $result = $CI->solr->executeSearch(true);

        if (isset($result['response']['numFound']) && $result['response']['numFound'] > 0)
        {
            NHMRC_Merged_File_to_RIFCS::$GRANT_ADMIN_LOOKUP[$name] = $result['response']['docs'][0]['key'];
            return $result['response']['docs'][0]['key'];
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
        $year_array = array();
        $keyword_array = array();
        $mapped_values = array();

        foreach($csv_values AS $idx => $csv_value)
        {
            $csv_value = htmlentities($csv_value);
            $heading = (isset($this->csv_headings[$idx]) ? $this->csv_headings[$idx] : 'NO_HEADING');
            if (strpos($heading, 'YR_') === 0)
            {
                $year_array[$heading] = $csv_value;
            }
            else if (strpos($heading,'RESEARCH_KW_') === 0 || strpos($heading,'HEALTH_KW_') === 0)
            {
                $keyword_array[$heading] = $csv_value;
            }
            else
            {
                if (strlen($csv_value) > 55 && substr($csv_value, 55,1) == " ")
                {
                    $csv_value = substr($csv_value,0,55) . substr($csv_value, 56);
                }
                $mapped_values[$heading] = $csv_value;
            }
        }

        $mapped_values['keywords'] = $keyword_array;
        $mapped_values['year_funding'] = $year_array;


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
    const CHUNK_SIZE = 100;

    function addRegistryObjectToChunkQueue($rifcs_xml)
    {

        $current_chunk = end($this->registryObjectChunks);
        //echo("array size:".count($current_chunk).NL);
        if (!$current_chunk || count($current_chunk) == self::CHUNK_SIZE)
        {
            $this->registryObjectChunks[] = array($rifcs_xml);
        }
        else
        {
            //echo("SUB CHUNK". count($this->registryObjectChunks[count($this->registryObjectChunks)-1]).NL);
            $this->registryObjectChunks[count($this->registryObjectChunks)-1][] = $rifcs_xml;
        }
    }

    function returnChunks()
    {
        $ro_xml = '';
        echo("registryObjectChunks: ". count($this->registryObjectChunks).NL);
        foreach ($this->registryObjectChunks AS $chunk)
        {
            echo("Chunks: ". count($chunk));
            $ro_xml .= implode(NL, $chunk);
        }
        //echo wrapRegistryObjects($ro_xml);
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
            $csv = str_getcsv($line, ",", '"', '\\');
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
