<?php


namespace ANDS;



use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\NotifyUtil;
use ANDS\Util\SolrIndex;
use Illuminate\Database\Eloquent\Model;
use ANDS\DataSource\Harvest as Harvest;
use ANDS\DataSource\DataSourceLog as DataSourceLog;

class DataSource extends Model
{
    protected $table = "data_sources";
    protected $primaryKey = "data_source_id";
    public $timestamps = false;
    protected $fillable = ['key', 'slug', 'title', 'record_owner', 'acronym'];

    public function dataSourceAttributes()
    {
        return $this->hasMany(DataSourceAttribute::class, "data_source_id", "data_source_id");
    }

    /**
     * Eloquent Accessor
     * usage: $this->id will return $this->data_source_id
     *
     * @return mixed
     */
    public function getIdAttribute()
    {
        return $this->data_source_id;
    }

    public function attributes()
    {
        return $this->dataSourceAttributes()->get()->map(function($item){
           return [$item->attribute => $item->value];
        })->collapse();
    }

    public function harvest()
    {
        return $this->hasOne(Harvest::class, "data_source_id", "data_source_id");
    }

    public function registryObjects()
    {
        return $this->hasMany(RegistryObject::class, "data_source_id", "data_source_id");
    }

    /**
     * Alias for getDataSourceAttributeValue
     *
     * @param $key
     * @return null
     */
    public function attr($key)
    {
       return $this->getDataSourceAttributeValue($key);
    }

    public function setDataSourceAttribute($key, $value)
    {
        if ($existingAttribute = DataSourceAttribute::where('attribute', $key)
            ->where('data_source_id', $this->data_source_id)->first()
        ) {
            $existingAttribute->value = $value;
            return $existingAttribute->save();
        } else {
            return DataSourceAttribute::create([
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

    public function getDataSourceAttributeValue($key)
    {
        $attribute = $this->getDataSourceAttribute($key);
        if ($attribute !== null) {
            return $attribute->value;
        }

        return null;
    }

    public function removeDataSourceAttribute($key)
    {
        return DataSourceAttribute::where('data_source_id', $this->data_source_id)->where('attribute', $key)->delete();
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

    public function dataSourceLog()
    {
        return $this->hasMany(DataSourceLog::class, "data_source_id", "data_source_id");
    }

    public function startHarvest()
    {
        $this->harvest->status = "SCHEDULED";
        $this->harvest->save();
    }

    public function stopHarvest()
    {
        $this->harvest->status = "STOPPED";
        $this->harvest->save();
    }

    public function getMeaningfulTitleAttribute()
    {
        return "{$this->title} ({$this->data_source_id}) ";
    }

    public function recount() {
        try {
            $this->calculateCounts();
        } catch (\Exception $e) {
            throw new \Exception("Failed to recount {$this->meaningfulTitle}: ". get_exception_msg($e));
        }
    }

    public function calculateCounts ()
    {
        // count_CLASS
        foreach (RegistryObject::$classes as $class) {
            $count = RegistryObject::where('data_source_id', $this->data_source_id)
                ->where('class', $class)->count();
            $this->setDataSourceAttribute("count_$class", $count);
//            var_dump($class, $count);
        }

        // count_STATUS
        foreach (RegistryObject::$statuses as $status) {
            $count = RegistryObject::where('data_source_id', $this->data_source_id)
                ->where('status', $status)->count();
            $this->setDataSourceAttribute("count_$status", $count);
        }

        // count_QL
        foreach (RegistryObject::$levels as $level) {
            $count = RegistryObject::where('data_source_id', $this->data_source_id)
                    ->whereHas('registryObjectAttributes', function($query) use ($level){
                        return $query
                            ->where('attribute', 'quality_level')
                            ->where('value', $level);
                    })->count();
            $this->setDataSourceAttribute("count_level_$level", $count);
        }

        // count_INDEXED
        $facets = SolrIndex::getFacets('data_source_id');
        $this->setDataSourceAttribute("count_INDEXED",
            array_key_exists($this->data_source_id, $facets) ? $facets[$this->data_source_id] : 0
        );
    }

    public function getCountsAttribute()
    {
        $result = [];

        // class
        foreach (RegistryObject::$classes as $class) {
            $result["count_$class"] = (int) $this->getDataSourceAttributeValue("count_$class");
        }

        // status
        foreach (RegistryObject::$statuses as $status) {
            $result["count_$status"] = (int) $this->getDataSourceAttributeValue("count_$status");
        }

        // ql
        foreach (RegistryObject::$levels as $level) {
            $result["count_level_$level"] = (int) $this->getDataSourceAttributeValue("count_level_$level");
        }

        // indexed
        $result["count_INDEXED"] = (int) $this->getDataSourceAttributeValue("count_INDEXED");

        // missing
        $result["count_MISSING"] = (int) $result['count_PUBLISHED'] - $result['count_INDEXED'];

        return $result;
    }

}