<?php


namespace ANDS\Registry\Providers;


use ANDS\DataSource;
use ANDS\Registry\Group;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\RegistryObject;
use ANDS\RegistryObjectAttribute;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\XMLUtil;
use Carbon\Carbon;
use MinhD\OAIPMH\Interfaces\OAIRepository;
use MinhD\OAIPMH\Record;
use MinhD\OAIPMH\Set;

class OAIRecordRepository implements OAIRepository
{
    public $dateFormat = "Y-m-d\\Th:m:s\\Z";
    protected $oaiIdentifierPrefix = "oai:ands.org.au::";

    public function identify()
    {
        $min = RegistryObjectAttribute::where('attribute', 'created')->min('value');
        $earliestDate = Carbon::createFromTimestamp($min)->format($this->getDateFormat());
        return [
            'repositoryName' => 'Australian National Data Services (ANDS)',
            'baseURL' => baseUrl(),
            'protocolVersion' => '2.0',
            'adminEmail' => 'services@ands.org.au',
            'earliestDateStamp' => $earliestDate,
            'deletedRecord' => 'transient',
            'granularity' => 'YYYY-MM-DDThh:mm:ssZ'
        ];
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
        return [
            [
                'metadataPrefix' => 'rif',
                'schema' => "http://services.ands.org.au/documentation/rifcs/1.3/schema/registryObjects.xsd",
                'metadataNamespace' => 'http://ands.org.au/standards/rif-cs/registryObjects
'
            ],
            [
                'metadataPrefix' => 'oai_dc',
                'schema' => "http://www.openarchives.org/OAI/2.0/oai_dc.xsd",
                'metadataNamespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/'
            ],
            [
                'metadataPrefix' => 'scholix',
                'schema' => 'https://raw.githubusercontent.com/scholix/schema/master/xsd/scholix.xsd',
                'metadataNamespace' => 'http://www.scholix.org'
            ]
        ];

        // TODO: scholix
    }

    public function listRecords($metadataFormat = null, $set = null, $options)
    {
        if ($metadataFormat == "scholix") {
            return $this->listScholixRecords($set, $options);
        }

        $registryObjects = $this->getRegistryObjects($options);
        $records = $registryObjects['records'];
        $total = $registryObjects['total'];

        $result = [];
        foreach ($records as $record) {
            $oaiRecord = new Record(
                $this->oaiIdentifierPrefix.$record->id,
                DatesProvider::getCreatedDate($record, $this->getDateFormat())
            );

            // set
            $oaiRecord = $this->addSets($oaiRecord, $record);

            // metadata TODO metadataPrefix
            $oaiRecord = $this->addMetadata($oaiRecord, $record, $metadataFormat);

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
        if ($metadataFormat == "scholix") {
            return $this->getScholixRecord($identifier);
        }

        $id = str_replace($this->oaiIdentifierPrefix, "", $identifier);
        $record = RegistryObjectsRepository::getRecordByID($id);

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

    private function addMetadata($oaiRecord, $record, $metadataFormat)
    {
        if ($metadataFormat == 'rif') {
            $metadata = "<registryObject />";
            $recordMetadata = MetadataProvider::getSelective($record, ['recordData']);
            if (array_key_exists('recordData', $recordMetadata)) {
                $metadata = XMLUtil::unwrapRegistryObject($recordMetadata['recordData']);
            }
            $oaiRecord->setMetadata($metadata);
        } elseif ($metadataFormat == "oai_dc") {
            // TODO DCI Provider?
        }
        return $oaiRecord;
    }

    public function listRecordsByToken($token)
    {
        // TODO: Implement listRecordsByToken() method.
    }

    public function listIdentifiers($metadataPrefix = null, $options)
    {
        // rif
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

        // TODO: scholix
    }

    private function getRegistryObjects($options)
    {
        $records = RegistryObject::where('status', 'PUBLISHED');

        if ($options['set']) {
            $set = $options['set'];
            $set = explode(':', $set);
            if ($set[0] == "class") {
                $records = $records->where('class', $set[1]);
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

    private function listScholixRecords($set = null, $options)
    {
        $limit = $options['limit'];
        $offset = $options['offset'];

        $records = Scholix::take($limit)->skip($offset);

        if ($options['set']) {
            $set = $options['set'];
            $set = explode(':', $set);
            if ($set[0] == "class") {
                $records = $records->where('registry_object_class', $set[1]);
            } elseif ($set[0] == "group") {
                $records = $records->where('registry_object_group', $set[1]);
            } elseif ($set[0] == "datasource") {
                $records = $records->where('registry_object_data_source_id', $set[1]);
            }
        }

        $total = $records->count();

        $result = [];
        foreach ($records->get() as $record) {
            $oaiRecord = new Record(
                $record->scholix_identifier,
                Carbon::parse($record->created_by)->format($this->getDateFormat())
            );

            // TODO: Set

            // metadata
            $oaiRecord->setMetadata($record->data);

            $result[] = $oaiRecord;
        }

        return [
            'total' => $total,
            'records' => $result,
            'limit' => $options['limit'],
            'offset' => $options['offset']
        ];
    }

    private function getScholixRecord($identifier)
    {
        $record = Scholix::where('scholix_identifier', $identifier)->first();

        if (!$record) {
            return null;
        }

        $oaiRecord = new Record($identifier, Carbon::parse($record->created_at)->format($this->getDateFormat()));

        // set
        $group = Group::where('title', $record->registry_object_group)->first();
        $ro = RegistryObject::find($record->registry_object_id);
        $dataSource = DataSource::find($record->registry_object_data_source_id);
        $sets = [
            new Set("class:". $ro->class, $ro->class),
            new Set("group:". $group->id, $group->title),
            new Set("datasource:". $dataSource->data_source_id, $dataSource->title)
        ];
        foreach ($sets as $set) {
            $oaiRecord->addSet($set);
        }

        $oaiRecord->setMetadata($record->data);

        return $oaiRecord;
    }
}