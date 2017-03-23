<?php
/**
 * Class:  DatasourcesHandler
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Registry\Handler;
use ANDS\DataSource;
use ANDS\DataSource\DataSourceLog;
use ANDS\DataSource\Harvest;
use ANDS\DataSourceAttribute;
use ANDS\Payload;
use ANDS\Registry\Importer;
use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use \Exception as Exception;

/**
 * Class DatasourcesHandler
 * @package ANDS\API\Registry\Handler
 */
class DatasourcesHandler extends Handler
{
    private $input = [];

    /**
     * DatasourceHandler constructor.
     * @param bool $params
     */
    public function __construct($params)
    {
        parent::__construct($params);
        initEloquent();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function handle()
    {
        $this->middleware();

        if ($this->params['identifier'] === false) {

            // POST api/registry/datasources
            if ($this->isPost()) {
                $newDataSource = $this->handleDataSourceCreation();
                return $newDataSource->toArray();
            }

            // GET api/registry/datasources
            return DataSource::all();
        }

        $identifier = $this->params['identifier'];
        $dataSource = DataSource::find($identifier);
        if (!$dataSource) {
            $dataSource = DataSource::where('slug', $identifier)
                ->get()->first();
        }
        if (!$dataSource) {
            $dataSource = DataSource::where('title', $identifier)
                ->get()->first();
        }

        if (!$dataSource) {
            throw new Exception("DataSource with identifier (id|slug) $identifier not found");
        }

        $dataSource->load('harvest');
        $dataSource->load('dataSourceAttributes');

        if ($this->params['object_module'] === false) {

            // DELETE api/registry/datasources/:id
            if ($this->isDelete()) {
                return $this->handleDataSourceDelete($dataSource);
            }

            // GET api/registry/datasources/:id
            return $dataSource;
        }

        switch ($this->params['object_module']) {
            case "harvest":
                return $this->handleHarvest($dataSource);
            case "attributes":
                return $this->handleAttribute($dataSource);
            case "records":
                return $this->handleRecords($dataSource);
            case "log":
                return $this->handleDataSourceLog($dataSource);
        }

        return null;
    }

    /**
     * @param DataSource $dataSource
     * @return mixed
     */
    private function handleDataSourceLog(DataSource $dataSource)
    {
        $dataSource->load('dataSourceLog');
        return $dataSource->dataSourceLog;
    }

    /**
     * @param DataSource $dataSource
     * @return mixed
     */
    private function handleHarvest(DataSource $dataSource)
    {
        // trigger harvest
        // PUT|POST api/registry/datasources/:id/harvest
        if ($this->isPost() || $this->isPut()) {
            $dataSource->startHarvest();
        }

        // cancel harvest
        // DELETE api/registry/datasources/:id/harvest
        if ($this->isDelete()) {
            $dataSource->stopHarvest();
            $dataSource->appendDataSourceLog(
                "Harvest stopped via API", "info", "IMPORTER"
            );
        }

        return $dataSource->harvest;
    }

    /**
     * @param DataSource $dataSource
     * @return array
     */
    private function handleRecords(DataSource $dataSource)
    {
        // define list
        $limit = $this->getInput('limit') !== null ? $this->getInput('limit') : 10;
        $offset = $this->getInput('offset') !== null ? $this->getInput('offset') : 0;

        $filters = [];
        $fields = ['status', 'class', 'type', 'group'];
        foreach ($fields as $field) {
            if ($this->getInput($field) !== null) {
                $filters[$field] = $this->getInput($field);
            }
        }

        $records = RegistryObjectsRepository::getRecordsByDataSource($dataSource, $limit, $offset, $filters);

        // delete all records in a datasource
        // DELETE api/registry/datasources/:id/records
        if ($this->isDelete()) {
            $task = Importer::instantDeleteRecords($dataSource, [
                'ids' => $records->pluck('registry_object_id')
            ]);
            return $task ? $task->toArray() : [];
        }

        // import records from url provided in GET or POST
        // POST api/registry/datasources/:id/records
        if ($this->isPost()) {
            if (!array_key_exists('url', $this->input)) {
                throw new Exception("Missing url. Input: " . implode(', ', array_keys($this->input)));
            }
            $url = $this->input['url'];
            $content = @file_get_contents($url);

            $batchID = "MANUAL-URL-".str_slug($url).'-'.time();
            Payload::write($dataSource->data_source_id, $batchID, $content);
            $task =  Importer::instantImportRecordFromBatchID($dataSource, $batchID);
            return $task->toArray();
        }

        // browse records
        return $records;
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    private function handleDataSourceCreation()
    {
        $missing = $this->getMissingInput(['key', 'title', 'record_owner']);
        if (count($missing) > 0) {
            throw new Exception("Missing POST fields: ". implode(", ", $missing));
        }

        $dataSource = DataSource::create([
            'key' => $this->getInput('key'),
            'title' => $this->getInput('title'),
            'record_owner' => $this->getInput('record_owner'),
            'slug' => url_title($this->getInput('title'))
        ]);

        return $dataSource;
    }

    /**
     * @param DataSource $dataSource
     * @return bool
     */
    private function handleDataSourceDelete(DataSource $dataSource)
    {
        // delete attributes
        $attributes = DataSourceAttribute::where('data_source_id', $dataSource->data_source_id)->delete();

        // delete logs
        $logs = DataSourceLog::where('data_source_id', $dataSource->data_source_id)->delete();

        // delete harvest
        $harvest = Harvest::where('data_source_id', $dataSource->data_source_id)->delete();

        // wipe all records from existence
        $ids = RegistryObject::where('data_source_id', $dataSource->data_source_id)
            ->get()->pluck('registry_object_id');
        $records = [];
        foreach ($ids as $id) {
            $records[$id] = RegistryObjectsRepository::completelyEraseRecordByID($id);
        }

        // TODO: wipe index as well
        $index = false;

        $ds = $dataSource->delete();
        return compact('attributes', 'logs', 'harvest', 'ds', 'records', 'index');
    }

    /**
     * @param DataSource $dataSource
     * @return array
     * @throws Exception
     */
    public function handleAttribute(DataSource $dataSource)
    {
        if($this->params['object_submodule'] === false) {

            //POST api/registry/datasources/:id/attributes
            //Creates new attributes
            if ($this->isPost()) {
                $missing = $this->getMissingInput(['attribute', 'value']);
                if (count($missing) > 0) {
                    return "Missing POST fields: ". implode(", ", $missing);
                }

                return DataSourceAttribute::create([
                    'attribute' => $this->input['attribute'],
                    'value' => $this->input['value'],
                    'data_source_id' => $dataSource->data_source_id
                ])->toArray();
            }

            return $dataSource->dataSourceAttributes;
        }

        //specific attribute
        $specific = $this->params['object_submodule'];
        $attribute = $dataSource
            ->getDataSourceAttribute($specific);

        if (!$attribute) {
            $attribute = DataSourceAttribute::find($specific);
        }

        if (!$attribute) {
            throw new Exception("No attribute $specific found in data source $dataSource->title");
        }

        // PUT api/registry/datasources/:id/attributes/[:id/:attribute]
        if ($this->isPut()) {
            $missing = $this->getMissingInput(['value']);
            if (count($missing) > 0) {
                return "Missing POST fields: ". implode(", ", $missing);
            }
            $attribute->value = $this->input['value'];
            $attribute->save();
            return $attribute;
        }

        if ($this->isDelete()) {
            return $attribute->delete();
        }

        // GET api/registry/datasources/:id/attributes/[:id/:attribute]
        return $attribute;
    }

    // Helpers

    /**
     * @param $key
     * @return mixed|null
     */
    public function getInput($key)
    {
        return array_key_exists($key, $this->input) ? $this->input[$key] : null;
    }

    /**
     * @param $required
     * @return array
     */
    public function getMissingInput($required)
    {
        return collect($required)->filter(function($item){
            return !array_key_exists($item, $this->input);
        })->toArray();
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === "POST";
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $_SERVER['REQUEST_METHOD'] === "DELETE";
    }

    /**
     * @return bool
     */
    public function isPut()
    {
        return $_SERVER['REQUEST_METHOD'] === "PUT";
    }

    public function middleware()
    {
        $this->input = $_GET;

        if (($this->isPost() || $this->isDelete() || $this->isPut())){

            if ($this->isPost()) {
                $this->input = array_merge($_GET, $_POST);
            }
            if ($this->isPut()) {
                parse_str(file_get_contents("php://input"),$post_vars);
                $this->input = array_merge($_GET, $post_vars);
            }

            $this->verifyAuth();
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function verifyAuth()
    {
        $whitelist = Config::get('app.api_whitelist_ip');

        if (!$whitelist) {
            throw new Exception("Whitelist IP not configured properly. This operation is unsafe.");
        }

        $ip = $this->getIPAddress();
        if (!in_array($ip, $whitelist)) {
            throw new Exception("IP: $ip is not whitelist for this behavior");
        }

        return true;
    }

    /**
     * @return string
     */
    private function getIPAddress()
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }

        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }

        if (isset($_SERVER["REMOTE_ADDR"])) {
            return $_SERVER["REMOTE_ADDR"];
        }

        // Run by command line??
        return "127.0.0.1";
    }
}