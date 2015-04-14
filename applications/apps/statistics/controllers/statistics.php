<?php

/**
 * Statistics Controller
 * Retrieves stores and gets various statistics of registry objects
 *
 * @author  Liz Woods <liz.woods@ands.org.au>
 */
class Statistics extends MX_Controller
{


    // Default page, containing current statistics
    function index()
    {

        $from = '';
        $to = '';

        if ($_GET = $this->input->get()) {
            $from = strtotime($_GET['dateFrom']);
            $to = strtotime($_GET['dateTo']);
        }

        if (!$from) $from = time();
        if (!$to) $to = time();


        $data['registry_statistics'] = $this->getRegistryStatistics($from, $to);
        $data['doi_statistics'] = $this->getDoiStatistics($from, $to);
        $data['pids_statistics'] = $this->getPidsStatistics($from, $to);

        $data['user_statistics'] = $this->getUserStatistics($from, $to);
        $data['title'] = "Statistics";
        $data['js_lib'] = array('core', 'ands_datepicker', 'statistics');
        $this->load->view('statistics', $data);
    }

    function getPublicationRelations()
    {
        //This function was written to produce an adhoc report about the numbers of collections with related publications Sept 2-14

        //Input csv is the result of the following query run on the production database

        // $query = $db->query("SELECT  DISTINCT(`record_data`.`registry_object_id`),`registry_objects`.`slug`, CONVERT(`record_data`.`data` USING utf8), `data_sources`.`title`
        // FROM `dbs_registry`.`record_data`,`dbs_registry`.`registry_objects`, `dbs_registry`.`data_sources`
        // WHERE `record_data`.`data` like '%<relatedInfo type="publication">%'
        // AND `record_data`.`current` = TRUE
        // AND `record_data`.`scheme` = 'rif'
        // AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
        // AND `registry_objects`.`data_source_id` = `data_sources`.`data_source_id`
        // AND `registry_objects`.`status` = 'PUBLISHED'");

        $handle = fopen("http://devl.ands.org.au/workareas/liz/publication_by_ds.csv", "r");
        $handle2 = fopen("../pubs/publication_by_ds_out.csv", "w+") or die("Unable to open file!");
        $row = 0;
        $pubs = 0;
        $collections = 0;
        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
            $row++;
            if ($row > 1) {

                $xml = simplexml_load_string($data[2]);
                $json = json_encode($xml);
                $array = json_decode($json, true);
                $type = '';

                if (isset($array['registryObject']['collection'])) {
                    $type = 'collection';
                    $collections++;

                    if (isset($array['registryObject'][$type]['relatedInfo'][0])) {
                        for ($i = 0; $i < count($array['registryObject'][$type]['relatedInfo']); $i++) {
                            $out[0] = $data[3];
                            $out[1] = "http://researchdata.ands.org.au/" . $data[1] . "/" . $data[0];

                            if ($array['registryObject'][$type]['relatedInfo'][$i]['@attributes']['type'] == 'publication') {

                                if (is_string($array['registryObject'][$type]['relatedInfo'][$i]['notes'])) {
                                    $out[2] = $array['registryObject'][$type]['relatedInfo'][$i]['notes'] . "\r\n";
                                } else {
                                    $out[2] = '';
                                }

                                if (is_string($array['registryObject'][$type]['relatedInfo'][$i]['title'])) {
                                    $out[3] = $array['registryObject'][$type]['relatedInfo'][$i]['title'] . "\r\n";
                                } else {
                                    $out[3] = '';
                                }

                                if (is_string($array['registryObject'][$type]['relatedInfo'][$i]['identifier'])) {
                                    $out[4] = $array['registryObject'][$type]['relatedInfo'][$i]['identifier'] . "\r\n";
                                } else {
                                    $out[4] = '';
                                }

                            }
                            $pubs++;
                            fputcsv($handle2, $out);
                        }
                    } else {
                        $out[0] = $data[3];
                        $out[1] = "http://researchdata.ands.org.au/" . $data[1] . "/" . $data[0];

                        if (is_string($array['registryObject'][$type]['relatedInfo']['notes'])) {
                            $out[2] = $array['registryObject'][$type]['relatedInfo']['notes'] . "\r\n";
                        } else {
                            $out[2] = '';
                        }
                        if (is_string($array['registryObject'][$type]['relatedInfo']['title'])) {
                            $out[3] = $array['registryObject'][$type]['relatedInfo']['title'] . "\r\n";
                        } else {
                            $out[3] = '';
                        }
                        if (is_string($array['registryObject'][$type]['relatedInfo']['identifier'])) {
                            $out[4] = $array['registryObject'][$type]['relatedInfo']['identifier'] . "\r\n";
                        } else {
                            $out[4] = '';
                        }

                        $pubs++;
                        fputcsv($handle2, $out);

                    }
                }

            } else {
                $out[0] = "Data source";
                $out[1] = "Collection URL";
                $out[2] = "Publication Notes";
                $out[3] = "Publication Title";
                $out[4] = "Publication Identifier";
                fputcsv($handle2, $out);
            }

        }
        echo $row . " is the objects found<br />";
        echo $collections . " is the collection count<br />";
        echo $pubs . " is publications listed<br />";

        fclose($handle);
        fclose($handle2);
    }

    function GetQuarterlyStats()
    {

        $start = array(1 => "2012-07-01T00:00:00.000Z", 2 => "2012-10-01T00:00:00.000Z", 3 => "2013-01-01T00:00:00.000Z", 4 => "2013-04-01T00:00:00.000Z");
        $end = array(1 => "2012-010-01T00:00:00.000Z", 2 => "2013-01-01T00:00:00.000Z", 3 => "2013-04-01T00:00:00.000Z", 4 => "2013-07-01T00:00:00.000Z");

        $this->load->library('solr');


        $this->solr->setOpt('start', 0);
        $this->solr->setOpt('rows', 1);
        $this->solr->setFacetOpt('field', 'group');
        $this->solr->setFacetOpt('field', 'data_source_key');
        $this->solr->setFacetOpt('sort', 'index');
        //print_r($data['solr_result']);
        for ($quarter = 1; $quarter < 5; $quarter++) {
            $this->solr->setOpt('q', '+record_created_timestamp:[' . $start[$quarter] . ' TO ' . $end[$quarter] . ']');
            $data['solr_result'] = $this->solr->executeSearch();
            $data['result'] = $this->solr->getResult();
            $data['numFound'] = $this->solr->getNumFound();
            $groups[$quarter] = $this->solr->getFacetResult('group');
            $datasource[$quarter] = $this->solr->getFacetResult('data_source_key');
        }
        $groups[0] = array();
        $datasource[0] = array();
        foreach ($groups as $groupvalue) {
            foreach ($groupvalue as $aGroup => $value) {
                if (isset($groups[0][$aGroup])) {
                    $groups[0][$aGroup] = $groups[0][$aGroup] . "," . $value;
                } else {
                    $groups[0][$aGroup] = $value;
                }
            }
        }
        foreach ($datasource as $groupvalue) {
            foreach ($groupvalue as $aGroup => $value) {
                if (isset($datasource[0][$aGroup])) {
                    $datasource[0][$aGroup] = $datasource[0][$aGroup] . "," . $value;
                } else {
                    $datasource[0][$aGroup] = $value;
                }
            }
        }
        echo " the group stats<br />";
        print("<pre>");
        print_r($groups[0]);
        print("</pre>");

        echo " the data_source stats<br />";
        print("<pre>");
        print_r($datasource[0]);
        print("</pre>");
    }

    // Returns the count by month of the registry objects
    function getRegistryStatistics($from, $to)
    {

        $number_of_months = 1;
        $newMonth = $to;
        $theMonth = date("m", $to);
        if ($from) {

            $number_of_months = date("n", $from) - date("n", $to) + 1;
            $newMonth = $from;
            $theMonth = date("m", $from);
            $theYear = date("Y", $from);
        }
        $registry_statistics = array();
        $newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
        $aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
        while ($aMonth <= $to) {
            //$CI =& get_instance();
            //$db =& $CI->db;
            $db = $this->load->database('registry', TRUE);
            $query = $db->query("SELECT COUNT(`registry_objects`.`class`) as theCount, `registry_objects`.`class`
				FROM `registry_object_attributes` , `registry_objects`
				WHERE `registry_objects`.`registry_object_id` = `registry_object_attributes`.`registry_object_id`
				AND `registry_object_attributes`.`attribute` = 'created' 
				AND `registry_objects`.`status` = 'PUBLISHED'
				AND `registry_object_attributes`.`value` < " . $newMonth . " GROUP BY `registry_objects`.`class`");


            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $registry_statistics[$month . " - " . $theYear][$row->class] = $row->theCount;
            }
            $theMonth++;
            if ($theMonth == 13) {
                $theMonth = 1;
                $theYear++;
            }
            $newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
            $aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
        }
        return $registry_statistics;
    }

    // Returns the count by month of the doi statistics
    private function getDoiStatistics($from, $to)
    {

        $doi_db = $this->load->database('dois', TRUE);

        $number_of_months = 1;
        $newMonth = $to;
        $theMonth = date("m", $to);
        if ($from) {

            $number_of_months = date("n", $from) - date("n", $to) + 1;
            $newMonth = $from;
            $theMonth = date("m", $from);
            $theYear = date("Y", $from);
        }
        $doi_statistics = array();
        $newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
        $aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
        while ($aMonth <= $to) {
            $query = $doi_db->query("SELECT	COUNT(*) as thecount
			FROM doi_objects 
			WHERE created_when <  '" . date("Y-m-d", $newMonth) . "' AND doi_id NOT LIKE '10.5072/%' AND status = 'ACTIVE' ");

            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $doi_statistics[$month . " - " . $theYear]['DOIs Minted'] = $row->thecount;
            }

            $query = $doi_db->query("SELECT COUNT(DISTINCT(app_id)) as thecount
			FROM doi_client  
			WHERE created_when <  '" . date("Y-m-d", $newMonth) . "'");

            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $doi_statistics[$month . " - " . $theYear]['Registered Clients'] = $row->thecount;
            }

            $query = $doi_db->query("SELECT COUNT(*) as thecount
			FROM activity_log 
			WHERE timestamp <  '" . date("Y-m-d", $newMonth) . "' AND activity = 'MINT' AND result = 'FAILURE' AND doi_id NOT LIKE '10.5072/%'");

            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $doi_statistics[$month . " - " . $theYear]['Minting failures'] = $row->thecount;
            }


            $theMonth++;
            if ($theMonth == 13) {
                $theMonth = 1;
                $theYear++;
            }
            $newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
            $aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);

        }
        return $doi_statistics;
    }

    private function getPidsStatistics($from, $to)
    {

        $doi_db = $this->load->database('pids', TRUE);

        $number_of_months = 1;
        $newMonth = $to;
        $theMonth = date("m", $to);
        if ($from) {

            $number_of_months = date("n", $from) - date("n", $to) + 1;
            $newMonth = $from;
            $aMonth = $from;
            $theMonth = date("m", $from);
            $theYear = date("Y", $from);
        }
        $pids_statistics = array();
        $newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
        $aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
        while ($aMonth <= $to) {
            $query = $doi_db->query("SELECT COUNT(*) as thecount FROM handles WHERE type='URL'AND timestamp < " . $newMonth);

            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $pids_statistics[$month . " - " . $theYear]['PIDs Minted'] = $row->thecount;
            }

            $query = $doi_db->query("SELECT COUNT(DISTINCT(app_id)) as thecount FROM trusted_client
			WHERE created_when <  CAST('" . date("Y-m-d", $newMonth) . "' AS timestamp with time zone)");
            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $pids_statistics[$month . " - " . $theYear]['Registered Clients'] = $row->thecount;
            }


            $theMonth++;
            if ($theMonth == 13) {
                $theMonth = 1;
                $theYear++;
            }
            $newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
            $aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
        }
        return $pids_statistics;
    }


    private function getUserStatistics($from, $to)
    {

        $roles_db = $this->load->database('roles', TRUE);
        $db = $this->load->database('registry', TRUE);
        $number_of_months = 1;
        $newMonth = $to;
        $theMonth = date("m", $to);
        if ($from) {

            $number_of_months = date("n", $from) - date("n", $to) + 1;
            $newMonth = $from;
            $aMonth = $from;
            $theMonth = date("m", $from);
            $theYear = date("Y", $from);
        }
        $user_statistics = array();
        $newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
        $aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
        while ($aMonth <= $to) {
            $dateTimeMonth = date("Y-m-d 00:00:00", $newMonth);
            $query = $roles_db->query("SELECT count(DISTINCT(trim(both '-' from trim(both ' ' from lower(substring(role_id from 1 for 4)))))) as thecount
			FROM `dbs_roles`.`roles` where `roles`.`role_type_id` = 'ROLE_ORGANISATIONAL'
			AND `roles`.`created_when` <= '" . $dateTimeMonth . "'");

            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $user_statistics[$month . " - " . $theYear]['Organisations'] = $row->thecount;
            }

            $query = $roles_db->query("SELECT COUNT(DISTINCT(roles.role_id)) as thecount
			FROM `dbs_roles`.`roles`, `dbs_roles`.`role_relations` 
			WHERE `roles`.`role_type_id` = 'ROLE_USER'
			AND `role_relations`.`parent_role_id` <> 'ORCA_CLIENT_LIAISON'
			AND `roles`.`role_id` = `role_relations`.`child_role_id`
			AND `roles`.`authentication_service_id` <> 'AUTHENTICATION_LDAP' 
			AND `roles`.`created_when` <='" . $dateTimeMonth . "'");
            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $user_statistics[$month . " - " . $theYear]['Users'] = $row->thecount;
            }

            $query = $roles_db->query("SELECT COUNT(*) as thecount
			FROM `dbs_roles`.`role_relations` 
			WHERE `role_relations`.`parent_role_id`='ORCA_SOURCE_ADMIN' 
			AND `role_relations`.`child_role_id` NOT LIKE '%@ands.org.au' 
			AND `role_relations`.`child_role_id` <> 'COSI_ADMIN'
			AND `role_relations`.`child_role_id` <> 'u4187959'
			AND `role_relations`.`child_role_id` <> 'u4958094'
			AND `role_relations`.`child_role_id` <> 'u4552016'
			AND `role_relations`.`created_when` <= '" . $dateTimeMonth . "'");
            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $user_statistics[$month . " - " . $theYear]['Data Source Adminstrators'] = $row->thecount;
            }

            $query = $db->query("SELECT COUNT(DISTINCT(`data_sources`.`data_source_id`)) as thecount
			FROM `dbs_registry`.`data_sources`, `dbs_registry`.`data_source_attributes`
			WHERE `data_source_attributes`.`attribute` = 'created'
			AND `data_sources`.`data_source_id` = `data_source_attributes`.`data_source_id`
			AND  `data_source_attributes`.`value` < " . $newMonth);
            foreach ($query->result() as $key => $row) {
                $month = date("M", $newMonth);
                $user_statistics[$month . " - " . $theYear]['Provider Organisations'] = $row->thecount;
            }

            $theMonth++;
            if ($theMonth == 13) {
                $theMonth = 1;
                $theYear++;
            }
            $newMonth = mktime(0, 0, 0, $theMonth + 1, 0, $theYear);
            $aMonth = mktime(0, 0, 0, $theMonth, 1, $theYear);
        }
        return $user_statistics;
    }

    function getCitationStatistics()
    {
        $registry_db = $this->load->database('registry', TRUE);
        $statistics_db = $this->load->database('statistics', TRUE);

        /*	$query = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as collection_count, `data_sources`.`data_source_id`
                                FROM `registry_objects`,`data_sources`
                                WHERE `registry_objects`.`data_source_id` = `data_sources`.`data_source_id`
                                AND `registry_objects`.`class` = 'collection'
                                AND `registry_objects`.`status` = 'PUBLISHED'
                                GROUP BY `registry_objects`.`data_source_id`; ");

            foreach($query->result() as $key=>$row)
            {
                $timestamp = time();
                $query = $statistics_db->query("INSERT INTO  `citations` (`data_source_id`,`collection_count`,`timestamp`) VALUES (".$row->data_source_id.", ".$row->collection_count.", ".$timestamp.")");
            }

            $citationMetadata_query = $registry_db->query("SELECT COUNT(DISTINCT(`record_data`.`registry_object_id`)) as citationMetadata_count,  `registry_objects`.`data_source_id`
                                FROM `record_data`, `registry_objects`
                                WHERE `record_data`.`data` LIKE '%citationMetadata%'
                                AND `record_data`.`scheme` = 'rif'
                                AND `record_data`.`current` = 'TRUE'
                                AND `registry_objects`.`status` = 'PUBLISHED'
                                AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
                                AND `registry_objects`.`class` = 'collection'
                                GROUP BY `registry_objects`.`data_source_id`");
            foreach($citationMetadata_query->result() as $key=>$row)
            {
                $query = $statistics_db->query("UPDATE  `citations` SET `citationMetadata_count` = ".$row->citationMetadata_count." WHERE `data_source_id` = ".$row->data_source_id);
            }


            $fullCitation_query = $registry_db->query("SELECT COUNT(DISTINCT(`record_data`.`registry_object_id`)) as fullCitation_count,  `registry_objects`.`data_source_id`
                                FROM `record_data`, `registry_objects`
                                WHERE `record_data`.`data` LIKE '%fullCitation%'
                                AND `record_data`.`scheme` = 'rif'
                                AND `record_data`.`current` = 'TRUE'
                                AND `registry_objects`.`status` = 'PUBLISHED'
                                AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
                                AND `registry_objects`.`class` = 'collection'
                                GROUP BY `registry_objects`.`data_source_id`");
            foreach($fullCitation_query->result() as $key=>$row)
            {
                $query = $statistics_db->query("UPDATE  `citations` SET `fullCitation_count` = ".$row->fullCitation_count." WHERE `data_source_id` = ".$row->data_source_id);
            }		*/
    }

    function collectCitationStatistics()
    {
        $registry_db = $this->load->database('registry', TRUE);
        $statistics_db = $this->load->database('statistics', TRUE);

        $query = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as collection_count, `data_sources`.`data_source_id`
							FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`data_sources`
							WHERE `registry_objects`.`data_source_id` = `data_sources`.`data_source_id`
							AND `registry_objects`.`class` = 'collection'
							AND `registry_objects`.`status` = 'PUBLISHED'
							GROUP BY `registry_objects`.`data_source_id`; ");

        foreach ($query->result() as $key => $row) {
            $timestamp = time();
            $query = $statistics_db->query("INSERT INTO  `citations` (`data_source_id`,`collection_count`,`timestamp`) VALUES (" . $row->data_source_id . ", " . $row->collection_count . ", " . $timestamp . ")");
        }

        $citationMetadata_query = $registry_db->query("SELECT COUNT(DISTINCT(`record_data`.`registry_object_id`)) as citationMetadata_count,  `registry_objects`.`data_source_id`
							FROM `dbs_registry`.`record_data`, `dbs_registry`.`registry_objects` 
							WHERE `record_data`.`data` LIKE '%citationMetadata%' 
							AND `record_data`.`scheme` = 'rif' 
							AND `record_data`.`current` = 'TRUE'
							AND `registry_objects`.`status` = 'PUBLISHED'							
							AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
							AND `registry_objects`.`class` = 'collection'
							GROUP BY `registry_objects`.`data_source_id`");
        foreach ($citationMetadata_query->result() as $key => $row) {
            $query = $statistics_db->query("UPDATE  `citations` SET `citationMetadata_count` = " . $row->citationMetadata_count . " WHERE `data_source_id` = " . $row->data_source_id);
        }


        $fullCitation_query = $registry_db->query("SELECT COUNT(DISTINCT(`record_data`.`registry_object_id`)) as fullCitation_count,  `registry_objects`.`data_source_id`
							FROM `dbs_registry`.`record_data`, `dbs_registry`.`registry_objects` 
							WHERE `record_data`.`data` LIKE '%fullCitation%' 
							AND `record_data`.`scheme` = 'rif' 
							AND `record_data`.`current` = 'TRUE'
							AND `registry_objects`.`status` = 'PUBLISHED'							
							AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
							AND `registry_objects`.`class` = 'collection'
							GROUP BY `registry_objects`.`data_source_id`");
        foreach ($fullCitation_query->result() as $key => $row) {
            $query = $statistics_db->query("UPDATE  `citations` SET `fullCitation_count` = " . $row->fullCitation_count . " WHERE `data_source_id` = " . $row->data_source_id);
        }
    }


    function collectIdentifierStatistics()
    {
        $registry_db = $this->load->database('registry', TRUE);
        $statistics_db = $this->load->database('statistics', TRUE);

        $this->load->library('solr');
        $query = $registry_db->query("SELECT  `data_sources`.`data_source_id`
							FROM `dbs_registry`.`data_sources`; ");
        $timestamp = time();
        foreach ($query->result() as $key => $row) {

            $doi_identifiers = 0;
            // use solr to get all identifiers of type doi for published collections
            $this->solr->setOpt('q', '+data_source_id:("' . $row->data_source_id . '") AND +class:collection AND +identifier_type:doi AND +status:PUBLISHED');


            $data['solr_result'] = $this->solr->executeSearch();
            $this->solr->setOpt('start', 0);
            $this->solr->setOpt('rows', 1);
            $data['result'] = $this->solr->getResult();
            $data['numFound'] = $this->solr->getNumFound();
            $doi_identifiers = $data['numFound'];

            // use solr to get all identifiers of type uri with a doi value for published collections
            $this->solr->setOpt('q', '+data_source_id:("' . $row->data_source_id . '") AND +class:collection AND +identifier_type:uri AND +identifier_value:*doi.org* AND +status:PUBLISHED');
            $data['solr_result'] = $this->solr->executeSearch();
            $this->solr->setOpt('start', 0);
            $this->solr->setOpt('rows', 1);
            $data['result'] = $this->solr->getResult();
            $data['numFound'] = $this->solr->getNumFound();
            $doi_identifiers = $doi_identifiers + $data['numFound'];


            $orcid_identifiers = 0;
            // use solr to get all identifiers of type orcid for published parties
            $this->solr->setOpt('q', '+data_source_id:("' . $row->data_source_id . '") AND +class:party AND +identifier_type:orcid AND +status:PUBLISHED');
            $data['solr_result'] = $this->solr->executeSearch();
            $this->solr->setOpt('start', 0);
            $this->solr->setOpt('rows', 1);
            $data['result'] = $this->solr->getResult();
            $data['numFound'] = $this->solr->getNumFound();
            $orcid_identifiers = $data['numFound'];

            // use solr to get all identifiers of type uri with a doi value for published collections
            $this->solr->setOpt('q', '+data_source_id:("' . $row->data_source_id . '") AND +class:party AND +identifier_type:uri AND +identifier_value:*orcid* AND +status:PUBLISHED');
            $data['solr_result'] = $this->solr->executeSearch();
            $this->solr->setOpt('start', 0);
            $this->solr->setOpt('rows', 1);
            $data['result'] = $this->solr->getResult();
            $data['numFound'] = $this->solr->getNumFound();
            $orcid_identifiers = $orcid_identifiers + $data['numFound'];

            $handle_identifiers = 0;
            // use solr to get all identifiers of type handle for published collections
            $this->solr->setOpt('q', '+data_source_id:("' . $row->data_source_id . '") AND +class:collection AND +identifier_type:handl* AND +status:PUBLISHED');
            $data['solr_result'] = $this->solr->executeSearch();
            $this->solr->setOpt('start', 0);
            $this->solr->setOpt('rows', 3);
            $data['result'] = $this->solr->getResult();
            $data['numFound'] = $this->solr->getNumFound();
            $handle_identifiers = $data['numFound'];

            // use solr to get all identifiers of type uri with a doi value for published collections
            $this->solr->setOpt('q', '+data_source_id:("' . $row->data_source_id . '") AND +class:party  AND +identifier_type:uri AND +identifier_value:*handle* AND +status:PUBLISHED');
            $data['solr_result'] = $this->solr->executeSearch();
            $this->solr->setOpt('start', 0);
            $this->solr->setOpt('rows', 3);
            $data['result'] = $this->solr->getResult();
            $data['numFound'] = $this->solr->getNumFound();
            $handle_identifiers = $handle_identifiers + $data['numFound'];

            $query = $statistics_db->query("INSERT INTO  `identifiers` (`data_source_id`,`doi`,`orcid`,`handle`,`timestamp`) VALUES (" . $row->data_source_id . "," . $doi_identifiers . ", " . $orcid_identifiers . ", " . $handle_identifiers . ", " . $timestamp . ")");
        }

    }

    function collectRelationshipStatistics()
    {
        $registry_db = $this->load->database('registry', TRUE);
        $statistics_db = $this->load->database('statistics', TRUE);

        $query = $registry_db->query("SELECT  `data_sources`.`data_source_id` FROM `dbs_registry`.`data_sources`; ");

        $timestamp = time();

        foreach ($query->result() as $key => $row) {
            $collectionPartyRelationship = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as collectionPartyCount, `registry_objects`.`data_source_id`
			FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`registry_object_relationships`
			WHERE `registry_objects`.`status`='PUBLISHED' 
			AND `registry_objects`.`class` = 'collection'
			AND `registry_objects`.`registry_object_id` = `registry_object_relationships`.`registry_object_id`
			AND `registry_object_relationships`.`related_object_class` = 'party'
			AND `registry_object_relationships`.`origin` = 'EXPLICIT'
			AND `registry_objects`.`data_source_id` = " . $row->data_source_id . ";");


            $result = $collectionPartyRelationship->result();
            $collectionPartyCount = $result[0]->collectionPartyCount;

            $collectionArcRelationship = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as collectinArcCount
			FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`registry_object_relationships`
			WHERE `registry_objects`.`status`='PUBLISHED' 
			AND `registry_objects`.`class` = 'collection'
			AND `registry_objects`.`registry_object_id` = `registry_object_relationships`.`registry_object_id`
			AND `registry_object_relationships`.`related_object_class` = 'activity'
			AND `registry_object_relationships`.`origin` = 'EXPLICIT'
			AND `registry_object_relationships`.`related_object_key` LIKE 'http://purl.org/au-research/grants/arc/%'
			AND `registry_objects`.`data_source_id` = " . $row->data_source_id . ";");

            $result = $collectionArcRelationship->result();
            $collectinArcCount = $result[0]->collectinArcCount;

            $collectionNhmrcRelationship = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as collectinNhmrcCount
			FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`registry_object_relationships`
			WHERE `registry_objects`.`status`='PUBLISHED' 
			AND `registry_objects`.`class` = 'collection'
			AND `registry_objects`.`registry_object_id` = `registry_object_relationships`.`registry_object_id`
			AND `registry_object_relationships`.`related_object_class` = 'activity'
			AND `registry_object_relationships`.`origin` = 'EXPLICIT'
			AND `registry_object_relationships`.`related_object_key` LIKE 'http://purl.org/au-research/grants/nhmrc/%'
			AND `registry_objects`.`data_source_id` = " . $row->data_source_id . ";");

            $result = $collectionNhmrcRelationship->result();
            $collectinNhmrcCount = $result[0]->collectinNhmrcCount;

            $collectionOtherRelationship = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as collectinOtherCount
			FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`registry_object_relationships`
			WHERE `registry_objects`.`status`='PUBLISHED' 
			AND `registry_objects`.`class` = 'collection'
			AND `registry_objects`.`registry_object_id` = `registry_object_relationships`.`registry_object_id`
			AND `registry_object_relationships`.`related_object_class` = 'activity'
			AND `registry_object_relationships`.`origin` = 'EXPLICIT'
			AND `registry_object_relationships`.`related_object_key` NOT LIKE 'http://purl.org/au-research/grants/arc/%'
			AND `registry_object_relationships`.`related_object_key` NOT LIKE 'http://purl.org/au-research/grants/nhmrc/%'
			AND `registry_objects`.`data_source_id` = " . $row->data_source_id . ";");

            $result = $collectionOtherRelationship->result();
            $collectinOtherCount = $result[0]->collectinOtherCount;

            $researcherCollectionRelationship = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as researcherCollectionCount
			FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`registry_object_relationships`, `dbs_registry`.`registry_object_attributes`
			WHERE `registry_objects`.`status`='PUBLISHED' 
			AND `registry_objects`.`class` = 'party'
			AND `registry_objects`.`registry_object_id` = `registry_object_relationships`.`registry_object_id`
			AND `registry_objects`.`registry_object_id` = `registry_object_attributes`.`registry_object_id`
			AND `registry_object_attributes`.`attribute` = 'type'
			AND `registry_object_attributes`.`value` = 'person'
			AND `registry_object_relationships`.`related_object_class` = 'collection'
			AND `registry_object_relationships`.`origin` = 'EXPLICIT'
			AND `registry_objects`.`data_source_id` = " . $row->data_source_id . ";");

            $result = $researcherCollectionRelationship->result();
            $researcherCollectionCount = $result[0]->researcherCollectionCount;


            $partyActivityRelationship = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as partyActivityCount, `registry_objects`.`data_source_id`
			FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`registry_object_relationships`
			WHERE `registry_objects`.`status`='PUBLISHED' 
			AND `registry_objects`.`class` = 'party'
			AND `registry_objects`.`registry_object_id` = `registry_object_relationships`.`registry_object_id`
			AND `registry_object_relationships`.`related_object_class` = 'activity'
			AND `registry_object_relationships`.`origin` = 'EXPLICIT'
			AND `registry_objects`.`data_source_id` = " . $row->data_source_id . ";");


            $result = $partyActivityRelationship->result();
            $partyActivityCount = $result[0]->partyActivityCount;

            $arcCollectionRelationship = $registry_db->query("SELECT COUNT(DISTINCT(`registry_objects`.`registry_object_id`)) as arcCollectionCount
			FROM `dbs_registry`.`registry_objects`, `dbs_registry`.`registry_object_relationships`
			WHERE `registry_objects`.`status`='PUBLISHED' 
			AND `registry_objects`.`class` = 'activity'
			AND `registry_objects`.`key` LIKE 'http://purl.org/au-research/grants/arc/%'
			AND `registry_objects`.`registry_object_id` = `registry_object_relationships`.`registry_object_id`
			AND `registry_object_relationships`.`related_object_class` = 'collection'
			AND `registry_object_relationships`.`origin` = 'EXPLICIT'
			AND `registry_objects`.`data_source_id` = " . $row->data_source_id . ";");

            $result = $arcCollectionRelationship->result();
            $arcCollectionCount = $result[0]->arcCollectionCount;

            $query = $statistics_db->query("INSERT INTO  `relationships`
				(`data_source_id`,`collection_party` ,`collection_arc`, `collection_nhmrc`, `collection_other`, `researcher_collection`, `party_activity`, `arc_collection`, `timestamp`)
				VALUES (" . $row->data_source_id . "," . $collectionPartyCount . "," . $collectinArcCount . ", " . $collectinNhmrcCount . "," . $collectinOtherCount . "," . $researcherCollectionCount . "," . $partyActivityCount . "," . $arcCollectionCount . "," . $timestamp . ")");
        }

    }

    function collectRelPubStatistics()
    {
        $compareStr = '%<relatedInfo type="publication">%';

        $statistics_db = $this->load->database('statistics', TRUE);

        $db = $this->load->database('registry', TRUE);

        $pubs = 0;

        $query = $db->query("SELECT  DISTINCT(`record_data`.`registry_object_id`),CONVERT(`record_data`.`data` USING utf8) as theRif, `data_sources`.`data_source_id`
        FROM `dbs_registry`.`record_data`,`dbs_registry`.`registry_objects`, `dbs_registry`.`data_sources`
        WHERE `record_data`.`data` like '" . $compareStr . "'
        AND `record_data`.`current` = TRUE
        AND `record_data`.`scheme` = 'rif'
        AND `record_data`.`registry_object_id` = `registry_objects`.`registry_object_id`
        AND `registry_objects`.`data_source_id` = `data_sources`.`data_source_id`
        AND `registry_objects`.`status` = 'PUBLISHED' ORDER BY `data_sources`.`data_source_id`");

        foreach ($query->result() as $key => $row) {

            $xml = simplexml_load_string($row->theRif);
            $json = json_encode($xml);
            $array = json_decode($json, true);
            $timestamp = time();

            if (isset($array['registryObject']['collection'])) {
                $type = 'collection';

                if (isset($array['registryObject'][$type]['relatedInfo'][0])) {
                    for ($i = 0; $i < count($array['registryObject'][$type]['relatedInfo']); $i++) {
                        $data_source_id = $row->data_source_id;
                        $registry_object_id = $row->registry_object_id;
                        if ($array['registryObject'][$type]['relatedInfo'][$i]['@attributes']['type'] == 'publication') {

                            if (is_string($array['registryObject'][$type]['relatedInfo'][$i]['notes'])) {
                                $notes = $statistics_db->escape($array['registryObject'][$type]['relatedInfo'][$i]['notes']);
                            } else {
                                $notes = "''";
                            }

                            if (is_string($array['registryObject'][$type]['relatedInfo'][$i]['title'])) {
                                $title = $statistics_db->escape($array['registryObject'][$type]['relatedInfo'][$i]['title']);
                            } else {
                                $title = "''";
                            }

                            if (is_string($array['registryObject'][$type]['relatedInfo'][$i]['identifier'])) {
                                $identifier = $statistics_db->escape($array['registryObject'][$type]['relatedInfo'][$i]['identifier']);
                            } else {
                                $identifier = "''";
                            }
                            //put it in the db
                            $query = $statistics_db->query("INSERT INTO  `related_publications` (`timestamp`,`data_source_id`,`registry_object_id`,`notes`,`title`,`identifier`) VALUES (" . $timestamp . "," . $data_source_id . "," . $registry_object_id . ", " . $notes . ", " . $title . ", " . $identifier . ")");
                            $pubs++;
                        }

                    }
                } else {
                    $data_source_id = $row->data_source_id;
                    $registry_object_id = $row->registry_object_id;
                    if (is_string($array['registryObject'][$type]['relatedInfo']['notes'])) {
                        $notes = $statistics_db->escape($array['registryObject'][$type]['relatedInfo']['notes']);
                    } else {
                        $notes = "''";
                    }
                    if (is_string($array['registryObject'][$type]['relatedInfo']['title'])) {
                        $title = $statistics_db->escape($array['registryObject'][$type]['relatedInfo']['title']);
                    } else {
                        $title = "''";
                    }
                    if (is_string($array['registryObject'][$type]['relatedInfo']['identifier'])) {
                        $identifier = $statistics_db->escape($array['registryObject'][$type]['relatedInfo']['identifier']);
                    } else {
                        $identifier = "''";
                    }
                    //put it in the db
                    $query = $statistics_db->query("INSERT INTO  `related_publications` (`timestamp`,`data_source_id`,`registry_object_id`,`notes`,`title`,`identifier`) VALUES (" . $timestamp . "," . $data_source_id . "," . $registry_object_id . ", " . $notes . ", " . $title . ", " . $identifier . ")");

                    $pubs++;


                }

            }

        }

    }

    function collectObjectCountStatistics()
    {
        $statistics_db = $this->load->database('statistics', TRUE);

        $this->load->library('solr');

        $this->solr->setOpt('start', 0);
        $this->solr->setOpt('rows', 1);
        $this->solr->setFacetOpt('field', 'data_source_id');
        $this->solr->setFacetOpt('sort', 'index');
        $this->solr->setFacetOpt('limit', -1);
        $data['solr_result'] = $this->solr->executeSearch();
        $data['result'] = $this->solr->getResult();
        $datasource = $this->solr->getFacetResult('data_source_id');

        $timestamp = time();

        foreach ($datasource as $data_source_id => $total) {

            $this->solr->setOpt('q', '+data_source_id:' . $data_source_id);
            $this->solr->setFacetOpt('field', 'class');
            $data['solr_result'] = $this->solr->executeSearch();
            $class = $this->solr->getFacetResult('class');

            //put it in the db
            $query = $statistics_db->query("INSERT INTO  `object_counts` (`timestamp`,`data_source_id`,`total`,`collection`,`party`,`activity`,`service`) VALUES (" . $timestamp . "," . $data_source_id . "," . $total . ", " . $class['collection'] . ", " . $class['party'] . ", " . $class['activity'] . ", " . $class['service'] . ")");

        }
    }

    /**
     * Request page hit data from Google Analytics
     *
     *
     * @param Int $year OPTIONAL: Start year
     * @param Int $month OPTIONAL: Start month
     * @param Int $day OPTIONAL: Start day
     */
    function getGoogleStatistics($year = null, $month = null, $day = null)
    {

        //scheduled job to run every day to retrieve google page views of registry objects for the previous day
        //if date variables are provided statistics from the provided date to yesterday will be retrieved

        $this->load->model('registry/registry_object/registry_objects', 'ro');
        $statistics_db = $this->load->database('statistics', TRUE);

        $ga = new gapi($this->config->item('ga_email'), $this->config->item('ga_password'));

        if (!$year) $year = date("Y", time());
        if (!$month) $month = date("m", time());
        if (!$day) $day = date("d", time()) - 1;

        $from = mktime(23, 59, 59, $month, $day, $year);

        while ($from < time()) {
            $date = date('Y-m-d', $from);

            $ga->requestReportData($this->config->item('ga_profile_id'), array('pagePath'), array('pageviews', 'uniquePageviews'), null, null, $date, $date, 1, 1000);

            foreach ($ga->getResults() as $result) {
                $slug = trim($result->getPagePath(), '/');
                $the_slug = explode("/", $slug);

                if (isset($slug[1])) {
                    $objectData = $this->ro->getByID($the_slug[1]);

                    if ($objectData->slug != '') {
                        $data = array(
                            //'id' => '',
                            'slug' => $objectData->slug,
                            'key' => $objectData->key,
                            'group' => $objectData->group,
                            'data_source' => $objectData->data_source_key,
                            'page_views' => $result->getPageviews(),
                            'unique_page_views' => $result->getUniquePageviews(),
                            'display_title' => $objectData->title,
                            'object_class' => $objectData->class,
                            'day' => $date
                        );
                    } else {
                        $objectData = $this->ro->getBySlug($the_slug[0]);

                        if ($objectData->slug != '') {
                            $data = array(
                               // 'id' => '',
                                'slug' => $objectData->slug,
                                'key' => $objectData->key,
                                'group' => $objectData->group,
                                'data_source' => $objectData->data_source_key,
                                'page_views' => $result->getPageviews(),
                                'unique_page_views' => $result->getUniquePageviews(),
                                'display_title' => $objectData->title,
                                'object_class' => $objectData->class,
                                'day' => $date
                            );
                        }
                    }
                } elseif (isset($slug[0])) {
                    $objectData = $this->ro->getBySlug($the_slug[0]);

                    if ($objectData->slug != '') {
                        $data = array(
                            //'id' => '',
                            'slug' => $objectData->slug,
                            'key' => $objectData->key,
                            'group' => $objectData->group,
                            'data_source' => $objectData->data_source_key,
                            'page_views' => $result->getPageviews(),
                            'unique_page_views' => $result->getUniquePageviews(),
                            'display_title' => $objectData->title,
                            'object_class' => $objectData->class,
                            'day' => $date
                        );
                    }

                }

                if (isset($data)) {
                    $statistics_db->insert('google_statistics', $data);
                    unset($data);
                }

            }

            $day = $day + 1;
            $from = mktime(23, 59, 59, $month, $day, $year);

        }
    }

    // Initialise
    function __construct()
    {
        parent::__construct();
    }


}