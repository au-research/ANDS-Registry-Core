<?php
/**
 * Class: SolrSpatial
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 * Date: 5/01/2016
 * Time: 11:51 AM
 */

namespace ANDS;


class SolrSpatial extends GenericSolrMigration
{

    /**
     * SolrSpatial constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setFields([
            [
                'name' => 'spatial_coverage_polygons',
                'type' => 'string',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'spatial_coverage_extents',
                'type' => 'string',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'spatial_coverage_extents_wkt',
                'type' => 'location_rpt',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            [
                'name' => 'spatial_coverage_centres',
                'type' => 'string',
                'stored' => 'true',
                'indexed' => true,
                'multiValued' => true
            ],
            ['name' => 'spatial_coverage_area_sum', 'type' => 'float', 'stored' => 'true', 'indexed' => true],
        ]);
    }

    /**
     * Replacing the default field type of location_rpt as well
     * @Note requires jts-1.8.0.jar installed in SOLR lib directory
     * @Override
     * @return mixed
     */
    function up()
    {
        $result = array();
        $result[] = parent::up();
        $result[] = $this->ci->solr->schema([
            'replace-field-type' => [
                'name' => 'location_rpt',
                'class' => 'solr.SpatialRecursivePrefixTreeFieldType',
                'spatialContextFactory' => 'com.spatial4j.core.context.jts.JtsSpatialContextFactory',
                'geo' => true,
                'distErrPct' => '0.025',
                'maxDistErr' => '0.000009',
                'units' => 'degrees'
            ]
        ]);
        return $result;
    }

    /**
     * @Override
     * @return mixed
     */
    function down()
    {
        $result = array();
        $result[] = parent::up();
        $result[] = $this->ci->solr->schema([
            'replace-field-type' => [
                'name' => 'location_rpt',
                'class' => 'solr.SpatialRecursivePrefixTreeFieldType',
                'geo' => true,
                'distErrPct' => '0.025',
                'maxDistErr' => '0.001'
            ]
        ]);
        return $result;
    }


}