<?php


namespace ANDS\Commands\Script;


use ANDS\DataSource;
use ANDS\Log\Log;
use ANDS\RecordData;

class TestScript extends GenericScript
{
    public function run()
    {
        Log::init();
//        Log::info("test", ['stuff' => 'something', 'q' => 'fish']);
//        Log::debug("A debug message");`

        $dataSource = DataSource::find(369);

        $idQuery = function($query) use ($dataSource) {
            $query->select('registry_object_id')->from('registry_objects')->where('data_source_id', '=', $dataSource->id);
        };

        $count = RecordData::whereIn('registry_object_id', $idQuery)->toSql();
//        Log::info("Deleting Record Data", ['count' => $count]);

        $this->log($count);
    }
}