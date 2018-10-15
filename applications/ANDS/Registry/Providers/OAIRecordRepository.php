<?php


namespace ANDS\Registry\Providers;


use ANDS\DataSource;
use ANDS\Registry\Group;
use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
use ANDS\Registry\Providers\DCI\DCI;
use ANDS\Registry\Providers\DublinCore\DublinCoreProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\RegistryObject;
use ANDS\RegistryObjectAttribute;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\XMLUtil;
use Carbon\Carbon;
use DCIMethod;
use ANDS\OAI\Exception\BadArgumentException;
use ANDS\OAI\Exception\CannotDisseminateFormat;
use ANDS\OAI\Exception\IdDoesNotExistException;
use ANDS\OAI\Interfaces\OAIRepository;
use ANDS\OAI\Exception\OAIException;
use ANDS\OAI\Record;
use ANDS\OAI\Set;

class OAIRecordRepository implements OAIRepository
{
    public $dateFormat = "Y-m-d\\Th:m:s\\Z";
    protected $oaiIdentifierPrefix = "oai:ands.org.au::";
    protected $formats = [
        "rif" => [
            'metadataPrefix' => 'rif',
            'schema' => "http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd",
            'metadataNamespace' => 'http://ands.org.au/standards/rif-cs/registryObjects'
        ],
        "oai_dc" => [
            'metadataPrefix' => 'oai_dc',
            'schema' => "http://www.openarchives.org/OAI/2.0/oai_dc.xsd",
            'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/'
        ],
        "scholix" => [
            'metadataPrefix' => 'scholix',
            'schema' => 'https://raw.githubusercontent.com/scholix/schema/master/xsd/scholix.xsd',
            'metadataNamespace' => 'http://www.scholix.org'
        ],
        "dci" => [
            'metadataPrefix' => 'dci',
            'schema' => 'https://clarivate.com/products/web-of-science/web-science-form/data-citation-index/',
            'metadataNamespace' => 'https://clarivate.com/products/web-of-science/web-science-form/data-citation-index/'
        ]
    ];

    public function identify()
    {
        $min = RegistryObjectAttribute::where('attribute', 'created')->min('value');
        $earliestDate = Carbon::createFromTimestamp($min)->format($this->getDateFormat());
        return [
            'repositoryName' => 'Australian National Data Services (ANDS)',
            'baseURL' => $this->getBaseUrl(),
            'protocolVersion' => '2.0',
            'adminEmail' => 'services@ands.org.au',
            'earliestDateStamp' => $earliestDate,
            'deletedRecord' => 'transient',
            'granularity' => 'YYYY-MM-DDThh:mm:ssZ'
        ];
    }

    public function getBaseUrl()
    {
        return baseUrl("api/registry/oai");
    }

    public function listSets($limit = 0, $offset = 0)
    {
        $sets = [];

        // class set
        $classes = ['collection', 'service', 'party', 'activity'];
        foreach ($classes as $class) {
            $sets[] = new Set("class:{$class}", $class);
        }

        // data source
        $dataSources = DataSource::all();
        foreach ($dataSources as $ds) {

            // name with dashes instead of space
            $title = htmlspecialchars($ds->title, ENT_XML1);
            $name = str_replace(" ", "-", $title);
            $sets[] = new Set("datasource:$name", $ds->title);

            // id
            $sets[] = new Set("datasource:{$ds->data_source_id}", $ds->title);
        }

        // group
        $groups = Group::all();
        foreach ($groups as $group) {

            // name with 0x20
            $title = htmlspecialchars($group->title, ENT_XML1);
            $name = str_replace(" ", "0x20", $title);
            $sets[] = new Set("group:$name", $group->title);

            // id
            $sets[] = new Set("group:{$group->id}", $group->title);
        }

        $total = count($sets);

        return compact('total', 'sets', 'limit', 'offset');
    }

    public function listMetadataFormats($identifier = null)
    {
        if ($identifier) {
            if(Scholix::where('scholix_identifier', $identifier)->first()) {
                return [ $this->formats["scholix"] ];
            };

            $id = str_replace($this->oaiIdentifierPrefix, "", $identifier);
            if (RegistryObject::find($id)) {
                return [ $this->formats["rif"], $this->formats['oai_dc'] ];
            }

            throw new IdDoesNotExistException();
        }
        return $this->formats;
    }

    public function listRecords($options)
    {
        $metadataPrefix = $options['metadataPrefix'];

        if (!in_array($metadataPrefix, array_keys($this->formats))) {
            throw new BadArgumentException();
        }

        if ($metadataPrefix == "scholix") {
            return $this->listScholixRecords($options);
        }

        if ($metadataPrefix == "dci") {
            return $this->listDCIRecords($options);
        }

        $registryObjects = $this->getRegistryObjects($options);
        $records = $registryObjects['records'];
        $total = $registryObjects['total'];

        $result = [];
        foreach ($records as $record) {
            $oaiRecord = new Record(
                $this->oaiIdentifierPrefix.$record->id,
                DatesProvider::getUpdatedAt($record, $this->getDateFormat())
            );

            // set
            $oaiRecord = $this->addSets($oaiRecord, $record);

            $oaiRecord = $this->addMetadata($oaiRecord, $record, $options['metadataPrefix']);

            $result[] = $oaiRecord;
        }

        return [
            'total' => $total,
            'records' => $result,
            'limit' => $options['limit'],
            'offset' => $options['offset']
        ];
    }

    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    public function listSetsByToken($token)
    {
        // TODO: Implement listSetsByToken() method.
    }

    public function getRecord($metadataFormat, $identifier)
    {
        if (!in_array($metadataFormat, array_keys($this->formats))) {
            throw new BadArgumentException();
        }

        if ($metadataFormat == "scholix") {
            return $this->getScholixRecord($identifier);
        }

        $id = str_replace($this->oaiIdentifierPrefix, "", $identifier);
        $record = RegistryObjectsRepository::getRecordByID($id);

        if (!$record) {
            // try to see if it's a scholix record
            $scholixRecord = Scholix::where('scholix_identifier', $identifier)->first();
            if ($scholixRecord) {
                throw new CannotDisseminateFormat();
            }
        }

        if (!$record) {
            return null;
        }

        $oaiRecord = new Record($identifier, DatesProvider::getCreatedDate($record, $this->getDateFormat()));

        $oaiRecord = $this->addSets($oaiRecord, $record);
        $oaiRecord = $this->addMetadata($oaiRecord, $record, $metadataFormat);

        return $oaiRecord;
    }

    private function addSets(Record $oaiRecord, RegistryObject $record)
    {
        $dataSource = $record->datasource;
        $escapedDSTitle = htmlspecialchars($dataSource->title, ENT_XML1);
        $groupName = $record->group;

        $sets = [
            new Set("class:{$record->class}", $record->class),
            new Set("datasource:". $dataSource->data_source_id, $escapedDSTitle),
        ];

        // group by id
        $group = Group::where('title', $groupName)->first();
        if ($group) {
            $sets[] = new Set("group:".$group->id, $group);
        }

        // data source backward compat
        $name = str_replace(" ", "-", $escapedDSTitle);
        $sets[] = new Set("datasource:$name", $escapedDSTitle);

        // group backward compat
        $name = str_replace(" ", "-", $groupName);
        $name = urlencode($name);
        $sets[] = new Set("group:$name", $groupName);

        foreach ($sets as $set) {
            $oaiRecord->addSet($set);
        }
        return $oaiRecord;
    }

    private function addMetadata(Record $oaiRecord, RegistryObject $record, $metadataFormat)
    {
        if ($metadataFormat == 'rif') {
            $metadata = "<registryObject />";
            $recordMetadata = MetadataProvider::getSelective($record, ['recordData']);
            if (array_key_exists('recordData', $recordMetadata)) {
                $metadata = XMLUtil::wrapRegistryObject(XMLUtil::unwrapRegistryObject($recordMetadata['recordData']), false);
            }
            $oaiRecord->setMetadata($metadata);
        } elseif ($metadataFormat == "oai_dc") {
            $metadata = DublinCoreProvider::get($record);
            $metadata = XMLUtil::stripXMLHeader($metadata);
            $metadata = trim($metadata);
            $oaiRecord->setMetadata($metadata);
        }
        return $oaiRecord;
    }

    public function listRecordsByToken($token)
    {
        // TODO: Implement listRecordsByToken() method.
    }

    public function listIdentifiers($options)
    {
        if ($options['metadataPrefix'] == "rif" || $options['metadataPrefix'] == "oai_dc") {
            $registryObjects = $this->getRegistryObjects($options);
            $result = [];

            foreach ($registryObjects['records'] as $record) {
                $oaiRecord = new Record(
                    "oai:ands.org.au:{$record->id}",
                    DatesProvider::getCreatedDate($record, $this->getDateFormat())
                );

                // set
                $oaiRecord->addSet(new Set("class:{$record->class}", $record->class));

                $result[] = $oaiRecord;
            }

            return [
                'total' => $registryObjects['total'],
                'records' => $result,
                'limit' => $options['limit'],
                'offset' => $options['offset']
            ];
        }

        if ($options['metadataPrefix'] == "scholix") {
            return $this->listIdentifiersScholix($options);
        }

        throw new BadArgumentException("Unknown metadataPrefix {$options['metadataPrefix']}");

    }

    private function listIdentifiersScholix($options)
    {
        $result = [];

        $records = $this->getScholixRecords($options);

        foreach ($records['records'] as $record) {
            $oaiRecord = new Record(
                $record->scholix_identifier,
                Carbon::parse($record->updated_at)->format($this->getDateFormat())
            );
            $oaiRecord = $this->addScholixSets($oaiRecord, $record);
            $result[] = $oaiRecord;
        }

        return [
            'total' => $records['total'],
            'records' => $result,
            'limit' => $options['limit'],
            'offset' => $options['offset']
        ];
    }

    private function getScholixRecords($options)
    {
        $records = Scholix::limit($options['limit'])->offset($options['offset']);

        // set
        if (array_key_exists('set', $options) && $options['set']) {
            $set = $options['set'];
            $set = explode(':', $set);

            if ($set[0] == "datasource") {
                $records = $records->where('registry_object_data_source_id', $set[1]);
            } else {
                throw new BadArgumentException();
            }
        }

        // from
        if (array_key_exists('from', $options) && $options['from']) {
            $records = $records->where(
                'updated_at', '>',
                    Carbon::parse($options['from'])->toDateTimeString()
            );
        }

        // until
        if (array_key_exists('until', $options) && $options['until']) {
            $records = $records->where(
                'updated_at', '<',
                    Carbon::parse($options['until'])->toDateTimeString()
            );
        }

        $count = $records->count();
        if ($count == 0) {
            $count = Scholix::count();
        }

        return [
            'total' => $count,
            'records' => $records->get()
        ];
    }

    private function getRegistryObjects($options)
    {
        $records = RegistryObject::where('status', 'PUBLISHED');

        if ($options['set']) {
            $set = $options['set'];
            $set = explode(':', $set);

            $opt = $set[0];
            $value = $set[1];

            switch ($opt) {
                case "class":
                    $records = $records->where('class', $value);
                    break;
                case "datasource":
                    if ($value = $this->getDataSourceID($value)) {
                        $records = $records->where('data_source_id', $value);
                    }
                    break;
                case "group":
                    if ($value = $this->getGroupName($value)) {
                        $records = $records->where('group', $value);
                    }
                    break;
            }
        }

        $total = $records->count();

        $limit = $options['limit'];
        $offset = $options['offset'];

        $records = $records->take($limit)->skip($offset);

        $records = $records->get();

        return [
            'records' => $records,
            'total' => $total
        ];
    }

    private function getDataSourceID($value)
    {
        // datasource:9
        if ($dataSource = DataSource::find($value)) {
            return $dataSource->data_source_id;
        }

        // Integrated-Marine-Observing-System
        $name = str_replace("-", " ", $value);
        if ($dataSource = DataSource::where('title', $name)->first()) {
            return $dataSource->data_source_id;
        }

        return null;
    }

    private function getGroupName($value)
    {
        // group:9
        if ($group = Group::find($value)) {
            return $group->title;
        }

        $name = str_replace("0x20", " ", $value);
        if ($group = Group::where('title', $name)->first()) {
            return $group->title;
        }

        return null;
    }

    /**
     * List Scholixable records
     * TODO: Remove $set param
     *
     * @param $options
     * @return array
     */
    private function listScholixRecords($options)
    {
        $records = $this->getScholixRecords($options);

        $result = [];
        foreach ($records['records'] as $record) {
            $oaiRecord = new Record(
                $record->scholix_identifier,
                Carbon::parse($record->updated_at)->format($this->getDateFormat())
            );
            $oaiRecord = $this->addScholixSets($oaiRecord, $record);
            $oaiRecord->setMetadata($record->data);

            $result[] = $oaiRecord;
        }

        return [
            'total' => $records['total'],
            'records' => $result,
            'limit' => $options['limit'],
            'offset' => $options['offset']
        ];
    }

    private function listDCIRecords($options)
    {
        $records = $this->getDCIRecords($options);

        $result = [];
        foreach ($records['records'] as $record) {
            $oaiRecord = new Record(
                $record->scholix_identifier,
                Carbon::parse($record->updated_at)->format($this->getDateFormat())
            );
            $oaiRecord = $this->addScholixSets($oaiRecord, $record);
            $oaiRecord->setMetadata($record->data);

            $result[] = $oaiRecord;
        }

        return [
            'total' => $records['total'],
            'records' => $result,
            'limit' => $options['limit'],
            'offset' => $options['offset']
        ];
    }

    private function getDCIRecords($options)
    {
        $records = DCI::limit($options['limit'])->offset($options['offset']);

        // set
        if (array_key_exists('set', $options) && $options['set']) {
            $set = $options['set'];
            $set = explode(':', $set);

            if ($set[0] == "datasource") {
                $records = $records->where('registry_object_data_source_id', $set[1]);
            } else {
                throw new BadArgumentException();
            }
        }

        // from
        if (array_key_exists('from', $options) && $options['from']) {
            $records = $records->where(
                'updated_at', '>',
                Carbon::parse($options['from'])->toDateTimeString()
            );
        }

        // until
        if (array_key_exists('until', $options) && $options['until']) {
            $records = $records->where(
                'updated_at', '<',
                Carbon::parse($options['until'])->toDateTimeString()
            );
        }

        $count = $records->count();
        if ($count == 0) {
            $count = DCI::count();
        }

        return [
            'total' => $count,
            'records' => $records->get()
        ];
    }

    private function getScholixRecord($identifier)
    {
        $record = Scholix::where('scholix_identifier', $identifier)->first();

        if (!$record) {
            // try to see if it's a registry object record
            $id = str_replace($this->oaiIdentifierPrefix, "", $identifier);
            $registryObjectRecord = RegistryObjectsRepository::getRecordByID($id);
            if ($registryObjectRecord) {
                throw new CannotDisseminateFormat();
            }
        }

        if (!$record) {
            return null;
        }

        $oaiRecord = new Record(
            $identifier,
            Carbon::parse($record->created_at)->format($this->getDateFormat())
        );
        $oaiRecord = $this->addScholixSets($oaiRecord, $record);
        $oaiRecord->setMetadata($record->data);

        return $oaiRecord;
    }

    private function addScholixSets(Record $oaiRecord, Scholix $record)
    {
//        $group = Group::where('title', $record->registry_object_group)->first();
//        $dataSource = DataSource::find($record->registry_object_data_source_id)->first();
//        $class = $record->getAttribute("registry_object_class");
        $dataSourceID = $record->getAttribute("registry_object_data_source_id");
        $sets = [
//            new Set("class:". $class, $class),
//            new Set("group:". $group->id, $group->title),
            new Set("datasource:". $dataSourceID, $dataSourceID)
        ];
        foreach ($sets as $set) {
            $oaiRecord->addSet($set);
        }
        return $oaiRecord;
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }
}