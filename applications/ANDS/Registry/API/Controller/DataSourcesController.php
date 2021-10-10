<?php
namespace ANDS\Registry\API\Controller;


use ANDS\DataSource;
use ANDS\DataSource\DataSourceLog;
use ANDS\DataSource\Harvest;
use ANDS\DataSourceAttribute;
use ANDS\Registry\API\Middleware\IPRestrictionMiddleware;
use ANDS\Registry\API\Request;
use ANDS\Registry\Importer;
use ANDS\RegistryObject;
use ANDS\Repository\DataSourceRepository;
use ANDS\Repository\RegistryObjectsRepository;

class DataSourcesController extends HTTPController implements RestfulController
{

    protected $withs = ['counts'];

    public function index()
    {
        $dataSources = DataSource::all();

        // with
        $withs = array_intersect(
            $this->withs,
            explode(',', Request::get('with'))
        );
        if (count($withs) > 0) {
            $dataSources = collect($dataSources)->map(function($ds) use ($withs){
                return $ds->append($withs);
            });
        }

        // action
        $action = Request::get('action');
        if ($action == "recount") {
            $dataSources->each(function($ds) {
                $ds->recount();
            });
        }

        return $dataSources;
    }

    public function show($any = null)
    {
        $dataSource = DataSourceRepository::getByAny($any);
        $dataSource->load('harvest');
        $dataSource->load('dataSourceAttributes');

        // with
        $withs = array_intersect(
            $this->withs,
            explode(',', Request::get('with'))
        );
        $dataSource->setAppends($withs);

        // action
        $action = Request::get('action');
        if ($action == "recount") {
            $dataSource->recount();
        }

        return $dataSource;
    }

    public function sync($any = null)
    {
        $dataSource = DataSourceRepository::getByAny($any);
        $task = Importer::syncDataSource($dataSource, $background = true);
        return $task->toArray();
    }

    public function update($any = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);

        $dataSource = DataSourceRepository::getByAny($any);
        $fields = Request::only(['title', 'key', 'acronym', 'record_owner']);


        $dataSource->update($fields);
        return $dataSource;
    }

    /**
     * TODO: DataSourceRepository::delete()
     * should also delete attributes and harvests possibly records
     *
     * @param null $any
     * @return array
     */
    public function destroy($any = null)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);

        $dataSource = DataSourceRepository::getByAny($any);

        // delete attributes
        $attributes = DataSourceAttribute::where('data_source_id', $dataSource->data_source_id)->delete();

        // delete logs
        $logs = DataSourceLog::where('data_source_id', $dataSource->data_source_id)->delete();

        // delete harvest
        $harvest = Harvest::where('data_source_id', $dataSource->data_source_id)->delete();

        // wipe all records from existence (TODO: make this an option, otherwise soft delete)
        $ids = RegistryObject::where('data_source_id', $dataSource->data_source_id)
            ->get()->pluck('registry_object_id');
        $records = [];
        foreach ($ids as $id) {
            $records[$id] = RegistryObjectsRepository::completelyEraseRecordByID($id);
        }

        // TODO: wipe index as well
        $index = false;

        $ds = $dataSource->delete();

        $dataSource->delete();
        return compact('attributes', 'logs', 'harvest', 'ds', 'records', 'index');
    }

    public function add()
    {
        $this->validate(['key', 'title', 'record_owner']);
        $this->middlewares([IPRestrictionMiddleware::class]);

        $dataSource = DataSourceRepository::createDataSource(
            Request::value('key'),
            Request::value('title'),
            Request::value('record_owner')
        );

        return $dataSource;
    }
}