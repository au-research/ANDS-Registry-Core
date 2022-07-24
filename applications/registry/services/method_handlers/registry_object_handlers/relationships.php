<?php
use ANDS\Mycelium\RelationshipSearchService;
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');

/**
 * Class:  relationships handler
 * Getting data from the RelationshipSearchService
 *
 * @author: Liz Woods <liz.woods@ardc.edu.au>
 */
class Relationships extends ROHandler
{
    // get a total of relationships to check if graph should be displayed
    private $related_count;
    /**
     * Primary handle function
     *
     * @return array
     */
    public function handle($params='')
    {
        $this->related_count = 0;
        return [
            'data' => $this->getRelatedData(),
            'software' => $this->getRelatedSoftware(),
            'publications' => $this->getRelatedPublication(),
            'programs' => $this->getRelatedPrograms(),
            'grants_projects' =>$this->getRelatedGrantsProjects(),
            'services' => $this->getRelatedService(),
            'websites' => $this->getRelatedWebsites(),
            'researchers' => $this->getRelatedResearchers(),
            'organisations' => $this->getRelatedOrganisations(),
            'related_count' => $this->related_count
        ];
    }

    /**
     * Obtain related data from SOLR
     * @return array
     */
    private function getRelatedData() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'collection',
            'not_to_type' => 'software',
            'to_title' => '*'
        ], ['boost_to_group' => $this->ro->group ,'rows' => 5]);
        $this->related_count += $result->total;
        return $result->toArray();
    }

    /**
     * Obtain related software from SOLR
     * @return array
     */
    private function getRelatedSoftware() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'collection',
            'to_type' => 'software',
            'to_title' => '*'
        ], ['boost_to_group' => $this->ro->group , 'rows' => 5]);
        $this->related_count += $result->total;
        return $result->toArray();
    }

    /**
     * Obtain related programs from SOLR
     * @return array
     */
    private function getRelatedPrograms() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'activity',
            'to_type' => 'program',
            'to_title' => '*'
        ], ['boost_to_group' => $this->ro->group , 'rows' => 5]);
        $this->related_count += $result->total;
        $programs = $result->toArray();

        //obtaining to_funder for each of the program
        foreach($programs['contents'] as $key=>$grant){
            // if the grant is not a related Object
            if ($grant['to_identifier_type'] === "ro:id") {
                $result2 = RelationshipSearchService::search([
                    'from_id' => $grant["to_identifier"],
                    'to_class' => 'party',
                    'relation_type' =>  ['isFunderOf', 'isFundedBy']
                ], ['rows' => 1]);
                $funded_by = $result2->toArray();
                // the funder's title is the to_title
                if (array_key_exists('contents', $funded_by) && count($funded_by['contents']) > 0) {
                    $programs['contents'][$key]["to_funder"] = $funded_by['contents'][0]["to_title"];
                }
            }else{ // RDA-758 it should still have a funder but we need to search from their end
                $result2 = RelationshipSearchService::search([
                    'to_identifier' => $grant["to_identifier"],
                    'from_class' => 'party',
                    'relation_type' =>  ['isFunderOf', 'isFundedBy']
                ], ['rows' => 1]);
                $funded_by = $result2->toArray();
                // the funder's title is the from_title
                if (array_key_exists('contents', $funded_by) && count($funded_by['contents']) > 0) {
                    $programs['contents'][$key]["to_funder"] = $funded_by['contents'][0]["from_title"];
                }
            }
        }
        return $programs ;
    }

    /**
     * Obtain related activity that are grants or projects from SOLR
     * @return array
     */
    private function getRelatedGrantsProjects() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'activity',
            'to_title' => '*',
            'not_to_type' => 'program'
        ], ['boost_to_group' => $this->ro->group, 'rows' => 5]);
        $this->related_count += $result->total;
        $grants_projects = $result->toArray();

        foreach($grants_projects['contents'] as $key=>$grant){
            if($grant["to_identifier_type"] === "ro:id"){
                $result2 = RelationshipSearchService::search([
                    'from_id' => $grant["to_identifier"],
                    'to_class' => 'party',
                    'relation_type' =>  ['isFunderOf', 'isFundedBy']
                ], ['rows' => 1]);
                $funded_by = $result2->toArray();
                // the funder's title is the to_title
                if(isset($funded_by['contents']) && count($funded_by['contents'])>0){
                    $grants_projects['contents'][$key]["to_funder"] = $funded_by['contents'][0]["to_title"];
                }
            }else{// RDA-758 it should still have a funder but we need to search from their end
                $result2 = RelationshipSearchService::search([
                    'to_identifier' => $grant["to_identifier"],
                    'from_class' => 'party',
                    'relation_type' =>  ['isFunderOf', 'isFundedBy']
                ], ['rows' => 1]);
                $funded_by = $result2->toArray();
                // the funder's title is the from_title
                if(isset($funded_by['contents']) && count($funded_by['contents'])>0){
                    $grants_projects['contents'][$key]["to_funder"] = $funded_by['contents'][0]["from_title"];
                }
            }
        }
        return $grants_projects ;
    }

    /**
     * Obtain related publications from SOLR
     * @return array
     */
    private function getRelatedPublication() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'publication'
        ], ['boost_to_group' => $this->ro->group, 'rows' =>100]);
        $this->related_count += $result->total;
        return $result->toArray();
    }

    /**
     * Obtain related services from SOLR
     * @return array
     */
    private function getRelatedService() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'service',
            'to_title' => '*'
        ], ['boost_to_group' => $this->ro->group, 'rows' => 5]);
        $this->related_count += $result->total;
        return $result->toArray();
    }

    /**
     * Obtain related websites from SOLR
     * @return array
     */
    private function getRelatedWebsites() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'website'
        ], ['boost_to_group' => $this->ro->group ,'rows' =>100]);
        $this->related_count += $result->total;
        return $result->toArray();
    }

    /**
     * Obtain related researchers from SOLR
     * relationships where there's a hasPrincipalInvestigator edge is ranked higher via boosted query
     * @return array
     */
    // RDA-627 make boost relation_type an array and boost decrease by the order in the array
    private function getRelatedResearchers() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'party',
            'not_to_type' => 'group',
            'to_title' => '*',
        ], ['boost_to_group' => $this->ro->group ,'boost_relation_type' =>
            ['Principal Investigator','hasPrincipalInvestigator','Chief Investigator'] ,
            'rows' => 5, 'sort' => 'score desc, to_title asc']);
        $this->related_count += $result->total;
        return $result->toArray();

    }

    /**
     * Obtain related organisations from SOLR
     * @return array
     */
    private function getRelatedOrganisations() {

        $result = RelationshipSearchService::search([
            'from_id' => $this->ro->id,
            'to_class' => 'party',
            'to_type' => 'group',
            'to_title' => '*'
        ], ['rows' => 5]);
        $this->related_count += $result->total;
        return $result->toArray();
    }

}
