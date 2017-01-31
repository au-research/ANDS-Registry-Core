<?php
/**
 * Class:  DatasourcesHandler
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API\Registry\Handler;
use ANDS\DataSource;
use ANDS\DataSourceAttribute;
use ANDS\Payload;
use ANDS\Registry\Importer;
use ANDS\RegistryObject;
use \Exception as Exception;

class DatasourcesHandler extends Handler
{
    protected $failedAuthMessage = "Unauthorised";

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
     */
    public function handle()
    {
        if (($this->isPost() || $this->isDelete()) && !$this->verifyAuth()) {
            return $this->failedAuthMessage;
        }

        if ($this->params['identifier'] === false) {
            if ($this->isPost()) {
                return $this->handleDataSourceCreation();
            }
            return DataSource::all();
        }

        $dataSource = DataSource::find($this->params['identifier']);
        if (!$dataSource) {
            $dataSource = DataSource::where('slug', $this->params['identifier'])
                ->get()->first();
        }

        if (!$dataSource) {
            return null;
        }

        $dataSource->attributes = $dataSource->attributes();
        $dataSource->load('harvest');

        if ($this->params['object_module'] === false) {
            if ($this->isDelete()) {
                return $this->handleDataSourceDelete($dataSource);
            }
            return $dataSource;
        }

        switch ($this->params['object_module']) {
            case "harvest":
                return $dataSource->harvest;
            case "attributes":
                return $this->handleAttributeModule($dataSource);
            case "records":
                return $this->handleRecords($dataSource);
            case "clear":
                return $this->handleClearDataSource($dataSource);
        }

        return null;
    }

    private function handleRecords(DataSource $dataSource)
    {
        if ($this->isDelete()) {
            return Importer::instantDeleteRecords($dataSource, [
                'ids' => RegistryObject::where('data_source_id', $dataSource->data_source_id)->get()->pluck('registry_object_id')
            ]);
        }

        if ($this->isPost()) {
            $url = $this->ci->input->get('url');
            $content = @file_get_contents($url);
            $batchID = "MANUAL-URL-".str_slug($url).'-'.time();
            Payload::write($dataSource->data_source_id, $batchID, $content);
            return Importer::instantImportRecordFromBatchID($dataSource, $batchID);
        }

        $offset = $this->ci->input->get('offset') ?: 0;
        $limit = $this->ci->input->get('limit') ?: 10;
        return RegistryObject::where('data_source_id', $dataSource->data_source_id)
            ->limit($limit)->offset($offset)->get();
    }

    private function handleDataSourceCreation()
    {
        $missing = $this->getMissingPOSTFields(['key', 'title', 'record_owner']);
        if (count($missing) > 0) {
            return "Missing POST fields: ". implode(", ", $missing);
        }

        $dataSource = DataSource::create([
            'key' => $_POST['key'],
            'title' => $_POST['title'],
            'record_owner' => $_POST['record_owner'],
            'slug' => url_title($_POST['title'])
        ]);

        return $dataSource;
    }

    private function handleDataSourceDelete(DataSource $dataSource)
    {
        DataSourceAttribute::where('data_source_id', $dataSource->data_source_id)->delete();

        $dataSource->delete();

        return true;
    }

    private function handleClearDataSource(DataSource $dataSource)
    {

    }

    public function getMissingPOSTFields($required)
    {
        return collect($required)->filter(function($item){
            return !array_key_exists($item, $_POST);
        })->toArray();
    }

    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === "POST";
    }

    public function isDelete()
    {
        return $_SERVER['REQUEST_METHOD'] === "DELETE";
    }

    public function verifyAuth()
    {
        $params = array_merge($_POST, $_GET);

        if (!array_key_exists("internal_api_key", $params)) {
            return false;
        }

        if ($params['internal_api_key'] != get_config_item("internal_api_key")) {
            return false;
        }

        return true;
    }

    /**
     * @param DataSource $dataSource
     * @return array
     */
    public function handleAttributeModule(DataSource $dataSource)
    {
        if($this->params['object_submodule'] === false) {
            return $dataSource->attributes;
        }

        return $dataSource->getDataSourceAttribute($this->params['object_submodule']);
    }
}