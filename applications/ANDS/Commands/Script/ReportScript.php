<?php


namespace ANDS\Commands\Script;


use ANDS\RegistryObject;
use Carbon\Carbon;

class ReportScript extends GenericScript
{
    private $rows = [];

    public function run()
    {
        $april2017 = Carbon::createFromDate(2017, 4, 1);
        $this->reportRecordsCreatedAfter($april2017);

        $headers = ['id', 'key', 'status', 'created', 'data_source_key', 'data_source_title', 'record_owner'];
        $this->table($this->rows, $headers);
        $count = count($this->rows);
        $this->log("Rows returned: {$count}");

        // CSV export
        $payload = array_merge([$headers], $this->rows);
//        $path = '/tmp/report_'.time().'.csv';
        $path = 'report_'.time().'.csv';
        $fp = fopen($path, 'w');
        foreach ($payload as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
    }

    public function reportRecordsCreatedAfter(Carbon $time)
    {
        $timestamp = $time->timestamp;
        $result = RegistryObject::where('status', 'PUBLISHED')
            ->whereHas('registryObjectAttributes',
                function ($query) use ($timestamp) {
                    return $query
                        ->where('attribute', 'created')
                        ->where('value', '>', $timestamp);
                });

//        $result = RegistryObject::inRandomOrder()->where('registry_object_id', '<', 3000);

        $this->log("Found {$result->count()} results matching records created after {$time->toDateTimeString()}");

        $result->chunk(2000, function($records) {
           foreach ($records as $record) {
               $created = Carbon::createFromTimestamp($record->getRegistryObjectAttributeValue('created'));
               $this->rows[] = [
                   'id' => $record->id,
                   'key' => $record->key,
                   'status' => $record->status,
                   'created' => $created->toDateTimeString(),
                   'data_source_key' => $record->datasource->key,
                   'data_source_title' => $record->datasource->title,
                   'record_owner' => $record->record_owner
               ];
               $this->log("Processed {$record->id}");
           }
        });
    }
}