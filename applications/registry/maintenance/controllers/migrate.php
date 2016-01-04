<?php

class Migrate extends MX_Controller {

	function solr() {
		set_exception_handler( 'json_exception_handler' );
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Content-type: application/json' );

		$this->load->library( 'solr' );

		$replace = [
			[ 'name' => 'id', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'required' => true ]
		];
		$fields  = [
			[ 'name' => 'slug', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'key', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'class', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'status', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'logo', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'data_source_id', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'data_source_key', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],

			[ 'name' => 'contributor_page', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'quality_level', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'tr_cited', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'update_timestamp', 'type' => 'date', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'record_created_timestamp', 'type' => 'date', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'record_modified_timestamp', 'type' => 'date', 'stored' => 'true', 'indexed' => true ],

			[ 'name' => 'tag', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true ],
			[ 'name' => 'tag_type', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true ],
			[ 'name' => 'tag_sort', 'type' => 'string', 'stored' => 'true', 'indexed' => true, 'multiValued' => true ],
			[
				'name'        => 'tag_search',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],

			[
				'name'        => 'text',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'fulltext',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],

			[ 'name' => 'group', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'group_sort', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'group_search', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true ],

			[ 'name' => 'type', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'type_sort', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'type_search', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true ],

			[ 'name' => 'license_class', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'access_rights', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],

			[ 'name' => 'title', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'title_search', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'display_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'list_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'list_title_sort', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'simplified_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[
				'name'    => 'simplified_title_search',
				'type'    => 'text_en_splitting',
				'stored'  => 'true',
				'indexed' => true
			],
			[ 'name' => 'alt_list_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'alt_display_title', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'alt_title_search', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true ],

			[ 'name' => 'description', 'type' => 'text_en_splitting', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'list_description', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[
				'name'        => 'description_value',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'description_type',
				'type'        => 'string',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],

			[
				'name'        => 'subject_value_resolved',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'subject_value_resolved_search',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 's_subject_value_resolved',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'subject_value_resolved_sort',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'subject_value_unresolved',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'subject_type',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'subject_vocab_uri',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'subject_anzsrcfor',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'subject_anzsrcseo',
				'type'        => 'text_en_splitting',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],

			[
				'name'        => 'identifier_value',
				'type'        => 'string',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'identifier_type',
				'type'        => 'string',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'    => 'identifier_value_search',
				'type'    => 'text_en_splitting',
				'stored'  => 'true',
				'indexed' => true
			],

			[
				'name'        => 'spatial_coverage_polygons',
				'type'        => 'string',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'spatial_coverage_extents',
				'type'        => 'location_rpt',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[
				'name'        => 'spatial_coverage_centres',
				'type'        => 'string',
				'stored'      => 'true',
				'indexed'     => true,
				'multiValued' => true
			],
			[ 'name' => 'spatial_coverage_area_sum', 'type' => 'sfloat', 'stored' => 'true', 'indexed' => true ],

			[ 'name' => 'date_to', 'type' => 'tdate', 'stored' => 'true', 'indexed' => true, 'multiValued' => true ],
			[ 'name' => 'date_from', 'type' => 'tdate', 'stored' => 'true', 'indexed' => true, 'multiValued' => true ],
			[ 'name' => 'earliest_year', 'type' => 'sint', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'latest_year', 'type' => 'sint', 'stored' => 'true', 'indexed' => true ],

			[ 'name' => 'theme_page', 'type' => 'string', 'stored' => 'true', 'indexed' => true ],
			[ 'name' => 'matching_identifier_count', 'type' => 'int', 'stored' => 'true', 'indexed' => true ],

		['name' => 'activity_status', 'type' => 'string', 'stored' => true, 'indexed' => true],
        ['name' => 'funding_amount', 'type' => 'sfloat', 'stored' => true, 'indexed' => true],
        ['name' => 'funding_scheme', 'type' => 'string', 'stored' => true, 'indexed' => true],
        ['name' => 'funding_scheme_search', 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],
        ['name' => 'researcher', 'type' => 'string', 'stored' => true, 'indexed' => true],
        ['name' => 'researchers_search', 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],
        ['name' => 'administering_institution', 'type' => 'string', 'stored' => true, 'indexed' => true],
        ['name' => 'administering_institution_search', 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],
        ['name' => 'funder', 'type' => 'string', 'stored' => true, 'indexed' => true],
        ['name' => 'funders_search', 'type' => 'text_en_splitting', 'stored' => true, 'indexed' => true],

		];

		$delete_fields = [ ];
		foreach ( $fields as $field ) {
			$delete_fields[] = [ 'name' => $field['name'] ];
		}

		$spatial_field_type = [
			'name'                  => 'location_rpt',
			'class'                 => 'solr.SpatialRecursivePrefixTreeFieldType',
			'spatialContextFactory' => 'com.spatial4j.core.context.jts.JtsSpatialContextFactory',
			'geo'                   => true,
			'distErrPct'            => '0.025',
			'maxDistErr'            => '0.000009',
			'units'                 => 'degrees'
		];

		$result = $this->solr->schema(
			[
				'delete-field'       => $delete_fields,
				'replace-field'      => $replace,
				'add-field'          => $fields,
				'replace-field-type' => $spatial_field_type
			]
		);
		echo $result;
	}


}