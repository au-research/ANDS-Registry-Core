<?php


namespace ANDS\Repository;


use ANDS\DataSource;

class DataSourceRepository
{
    public static function getByKey($key)
    {
        return DataSource::where('key', $key)->first();
    }

    /**
     * @param $data_source_id
     * @return DataSource
     */
    public static function getByID($data_source_id)
    {
        return DataSource::where('data_source_id', $data_source_id)->first();
    }

    /**
     * Return a data source by id, slug, title and key
     *
     * @param $any
     * @return DataSource
     * @throws \Exception
     */
    public static function getByAny($any)
    {
        $dataSource = DataSource::find($any);

        if (!$dataSource) {
            $dataSource = DataSource::where('slug', $any)
                ->get()->first();
        }

        if (!$dataSource) {
            $dataSource = DataSource::where('title', $any)
                ->get()->first();
        }

        if (!$dataSource) {
            $dataSource = DataSource::where('key', $any)
                ->get()->first();
        }

        if (!$dataSource) {
            throw new \Exception("DataSource with identifier (id|slug) $any not found");
        }

        return $dataSource;
    }

    /**
     * Create a new blank data source
     *
     * @param $key
     * @param $title
     * @param $owner
     * @return DataSource
     */
    public static function createDataSource($key, $title, $owner)
    {
        $dataSource = DataSource::create([
            'key' => $key,
            'title' => $title,
            'record_owner' => $owner,
            'slug' => url_title($title)
        ]);

        $attributes = [
            'provider_type' => 'rif',
            'uri' => 'http://',
            'harvest_method'=>'GETHarvester',
            'allow_reverse_internal_links' => 1,
            'allow_reverse_external_links' => 1,
            'manual_publish' => 0,
            'qa_flag' => 1,
            'create_primary_relationships' => 0,
            'assessment_notify_email_addr' => '',
            'created' => '',
            'updated' => '',
            'export_dci' => 0,
            'crosswalks' => '',
            'title' => $title,
            'record_owner' => $owner,
            'contact_name'=>' ',
            'contact_email'=>' ',
            'notes'=>'',
            'harvest_date' => '',
            'oai_set' => '',
            'advanced_harvest_mode' => 'STANDARD',
            'harvest_frequency' => '',
            'metadataPrefix' => '',
            'xsl_file' => '',
            'user_defined_params' => ''
        ];

        foreach ($attributes as $key => $value) {
            $dataSource->setDataSourceAttribute($key, $value);
        }

        return $dataSource;
    }
}