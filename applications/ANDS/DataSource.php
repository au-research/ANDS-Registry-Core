<?php


namespace ANDS;



use ANDS\Util\NotifyUtil;
use Illuminate\Database\Eloquent\Model;
use ANDS\DataSource\Harvest as Harvest;
use ANDS\DataSource\DataSourceLog as DataSourceLog;

class DataSource extends Model
{
    protected $table = "data_sources";
    protected $primaryKey = "data_source_id";

    public function dataSourceAttributes()
    {
        return $this->hasMany(DataSourceAttribute::class, "data_source_id", "data_source_id");
    }

    public function attr($key)
    {
        return DataSourceAttribute::where('data_source_id', $this->data_source_id)
            ->where('attribute', $key)->first()->value;

        // Alternative way to get attributes, maybe slower
        /*return $this->dataSourceAttributes->filter(function($value) use ($key){
            return $value->attribute == $key;
        })->first()->value;*/
    }


    public function setDataSourceAttribute($key, $value)
    {
        if ($existingAttribute = DataSourceAttribute::where('attribute', $key)
            ->where('data_source_id', $this->data_source_id)->first()
        ) {
            $existingAttribute->value = $value;
            return $existingAttribute->save();
        } else {
            return RegistryObjectAttribute::create([
                'data_source_id' => $this->data_source_id,
                'attribute' => $key,
                'value' => $value
            ]);
        }
    }

    public function getDataSourceAttribute($key)
    {
        return DataSourceAttribute::where('data_source_id', $this->data_source_id)
            ->where('attribute', $key)->first();
    }

    public function getHarvest($harvest_id){
        return Harvest::where('data_source_id', $this->data_source_id)->where('harvest_id', $harvest_id)->first();
    }

    public function addHarvest($status , $next_run, $mode){
        return Harvest::create([
            'data_source_id' => $this->data_source_id,
            'status' => $status,
            'next_run' => $next_run,
            'mode' => $mode
        ]);
    }

    public function appendDataSourceLog($log, $type, $class, $harvest_error_type = "") {
        $logContent = [
            'data_source_id' => $this->data_source_id,
            'log' => $log,
            'date_modified' => time(),
            'type' => $type,
            'class' => $class,
            'harvester_error_type' => $harvest_error_type
        ];

        NotifyUtil::notify(
            'datasource.'.$this->data_source_id.'.log',
            json_encode($logContent, true)
        );

        return DataSourceLog::create($logContent);
    }
}