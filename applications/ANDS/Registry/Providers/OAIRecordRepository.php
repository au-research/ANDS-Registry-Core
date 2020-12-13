<?php


namespace ANDS\Registry\Providers;


use ANDS\DataSource;
use ANDS\OAI\Exception\NoRecordsMatch;
use ANDS\Registry\Group;
use ANDS\Registry\Providers\DCI\DataCitationIndexProvider;
use ANDS\Registry\Providers\DCI\DCI;
use ANDS\Registry\Providers\DublinCore\DublinCoreProvider;
use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\Scholix\Scholix;
use ANDS\RegistryObject;
use ANDS\RegistryObjectAttribute;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
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
use \ANDS\Registry\Schema;
use \ANDS\Registry\Versions;
use ANDS\RegistryObject\RegistryObjectVersion;
use Illuminate\Database\Capsule\Manager as Capsule;

class OAIRecordRepository implements OAIRepository
{
    public $dateFormat = "Y-m-d\\Th:i:s\\Z";
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
            'adminEmail' => 'services@ardc.edu.au',
            'earliestDatestamp' => $earliestDate,
            'deletedRecord' => 'transient',
            'granularity' => 'YYYY-MM-DDThh:mm:ssZ'
        ];
    }

    public function getBaseUrl()
    {
        return baseUrl("api/registry/oai");
    }

    /**
     * Returns the response for ListSets verb
     *
     * Get all possible sets and then limit, offset them
     * There's no pagination natively support for this since there's no
     * table for sets
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function listSets($limit = 0, $offset = 0)
    {
        $sets = [];

        // class set
        $classes = ['collection', 'service', 'party', 'activity'];
        foreach ($classes as $class) {
            $sets[] = new Set("class:{$class}", $class);
        }

        foreach ($dataSources = DataSource::orderBy('title')->get() as $ds) {
            $sets[] = new Set("datasource:{$ds->data_source_id}", $ds->title);
        }

        foreach ($groups = Group::orderBy('title')->get() as $group) {
            $sets[] = new Set("group:{$group->id}", $group->title);
        }

        $total = count($sets);

        $sets = array_splice($sets, $offset, $limit);

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
        return $this->getFormats();
    }

    public function listRecords($options)
    {
        $metadataPrefix = $options['metadataPrefix'];

        if (!in_array($metadataPrefix,  array_keys($this->getFormats()))) {
            throw new BadArgumentException();
        }

        if ($metadataPrefix == "scholix") {
            return $this->listScholixRecords($options);
        }

        if ($metadataPrefix == "dci") {
            return $this->listDCIRecords($options);
        }

        if ($metadataPrefix == "rif" || $metadataPrefix == "oai_dc") {
            $registryObjects = $this->getRegistryObjects($options);
            $records = $registryObjects['records'];
            $total = $registryObjects['total'];

            $result = [];
            foreach ($records as $record) {
                $oaiRecord = new Record(
                    $this->oaiIdentifierPrefix . $record->id,
                    DatesProvider::getUpdatedAt($record, $this->getDateFormat(), 'UTC')
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
        // TODO change it to elseif ($metadataPrefix != 'rif') and
        // provide any schema from the 'schemas' table that are exportable
        else {
            return $this->listAltSchemaVersions($options);
        }


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

        if (!in_array($metadataFormat, array_keys($this->getFormats()))) {
            throw new BadArgumentException();
        }

        if ($metadataFormat == "scholix") {
            return $this->getScholixRecord($identifier);
        }

        if (!in_array($metadataFormat, array_keys($this->formats))) {

            $record = $this->getAltSchemaVersion($identifier, $metadataFormat);

            if (!$record || sizeof($record) == 0)  {
                return null;
            }

            $record = $record[0];
            $oaiRecord = new Record(
                $record->key,
                Carbon::parse($record->updated_at)->setTimezone('UTC')->format($this->getDateFormat())
            );
            $oaiRecord = $this->addAltSchemaVersionsSets($oaiRecord, $record);
            $oaiRecord->setMetadata($record->data);

            return $oaiRecord;
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
        $groupName = $record->group;

        $oaiRecord
            ->addSet(new Set("class:{$record->class}"))
            ->addSet(new Set("datasource:". $dataSource->data_source_id))
            ->addSet(new Set("datasource:". $this->nameBackwardCompat($dataSource->title)));

        if ($group = Group::where('title', $groupName)->first()) {
            $oaiRecord
                ->addSet(new Set("group:".$group->id))
                ->addSet(new Set("group:".$this->nameBackwardCompat($group->title)));
            if ($this->groupNameBWCompat($group->title) != $this->nameBackwardCompat($group->title)) {
                $oaiRecord->addSet(new Set("group:" . $this->groupNameBWCompat($group->title)));
            }
        }

        return $oaiRecord;
    }

    private function nameBackwardCompat($name)
    {
        return str_replace(" ", "-", $name);
    }

    private function groupNameBWCompat($name)
    {
        $name = str_replace(" ", "0x20", $name);
        $name = urlencode($name);
        return $name;
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
        } elseif ($metadataFormat == "dci") {
            if ($dci = DCI::where('registry_object_id', $record->id)->first()) {
                $oaiRecord->setMetadata($dci->data);
            }
        } else{
            $schema = Schema::where('prefix', $metadataFormat)->first();
            $altVersionsIDs = RegistryObjectVersion::where('registry_object_id', $record->id)->get()->pluck('version_id')->toArray();
            $altRecord = null;
            if (count($altVersionsIDs) > 0) {
                $altRecord = Versions::wherein('id', $altVersionsIDs)->where("schema_id", $schema->id)->first();
            }
            if ($altRecord) {
                $oaiRecord->setMetadata($altRecord->data);
            }
        }
        return $oaiRecord;
    }

    public function listRecordsByToken($token)
    {
        // TODO: Implement listRecordsByToken() method.
    }

    public function listIdentifiers($options)
    {
        if (in_array($options['metadataPrefix'], ['rif', 'oai_dc'])) {
            $registryObjects = $this->getRegistryObjects($options);
            $result = [];

            foreach ($registryObjects['records'] as $record) {
                $oaiRecord = new Record(
                    $this->oaiIdentifierPrefix.$record->id,
                    DatesProvider::getUpdatedAt($record, $this->getDateFormat(), 'UTC')
                );

                // set
                $oaiRecord = $this->addSets($oaiRecord, $record);

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

        if ($options['metadataPrefix'] == "dci") {
            return $this->listIdentifiersDCI($options);
        }

        else{
            return $this->listIdentifiersAltSchema($options);
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
                Carbon::parse($record->updated_at)->setTimezone('UTC')->format($this->getDateFormat())
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

    private function listIdentifiersDCI($options)
    {
        $result = [];

        $records = $this->getDCIRecords($options);

        foreach ($records['records'] as $record) {
            $oaiRecord = new Record(
                $this->oaiIdentifierPrefix.$record->registryObject->id,
                Carbon::parse($record->updated_at)->setTimezone('UTC')->format($this->getDateFormat())
            );
            $oaiRecord = $this->addDCISets($oaiRecord, $record);
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

        if (array_key_exists('set', $options) && $options['set']) {
            $set = $options['set'];
            $set = explode(':', $set);

            $opt = $set[0];
            $value = $set[1];

            switch ($opt) {
                case "datasource":
                    if ($value = $this->getDataSourceID($value)) {
                        $records = $records->where('registry_object_data_source_id', $value);
                    }
                    break;
                case "group":
                    if ($value = $this->getGroupName($value)) {
                        $records = $records->where('registry_object_group', $value);
                    }
                    break;
            }
        }

        // from
        if (array_key_exists('from', $options) && $options['from']) {
            $records = $records->where(
                'updated_at', '>=',
                    DatesProvider::parseUTCToLocal($options['from'])->toDateTimeString()
            );
        }

        // until
        if (array_key_exists('until', $options)) {
            $until = Carbon::parse($options['until'], 'UTC');
            $until = $until->isStartOfDay() ? $until->addDay(1) : $until;
            $until = $until->setTimezone(Config::get('app.timezone'));
            if (array_key_exists('until', $options) && $options['until']) {
                $records = $records->where(
                    'updated_at', '<=',
                    $until->toDateTimeString()
                );
            }
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

        // from
        if (array_key_exists('from', $options) && $options['from']) {
            $records = $records->where(
                'modified_at', '>=',
                DatesProvider::parseUTCToLocal($options['from'])->toDateTimeString()
            );
        }

        // until
        if (array_key_exists('until', $options)) {
            $until = Carbon::parse($options['until'], 'UTC');
            $until = $until->isStartOfDay() ? $until->addDay(1) : $until;
            $until = $until->setTimezone(Config::get('app.timezone'));
            if (array_key_exists('until', $options) && $options['until']) {
                $records = $records->where(
                    'modified_at', '<=',
                    $until->toDateTimeString()
                );
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

        // group:Curtin-University
        if ($group = Group::where('title', str_replace('-', ' ', $value))->first()) {
            return $group->title;
        }

        // group:Curtin0x20University
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
                Carbon::parse($record->updated_at)->setTimezone('UTC')->format($this->getDateFormat())
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
                $this->oaiIdentifierPrefix.$record->registryObject->id,
                Carbon::parse($record->updated_at)->setTimezone('UTC')->format($this->getDateFormat())
            );

            $oaiRecord = $this->addDCISets($oaiRecord, $record);
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
            $set = urldecode($set);
            $set = explode(':', $set);

            $opt = $set[0];
            $value = $set[1];

            switch ($opt) {
                case "datasource":
                    if ($value = $this->getDataSourceID($value)) {
                        $records = $records->where('registry_object_data_source_id', $value);
                    } else {
                        throw new NoRecordsMatch();
                    }
                    break;
                case "group":
                    if ($value = $this->getGroupName($value)) {
                        $records = $records->where('registry_object_group', $value);
                    } else {
                        throw new NoRecordsMatch();
                    }
                    break;
            }
        }

        // from
        if (array_key_exists('from', $options) && $options['from']) {
            $records = $records->where(
                'updated_at', '>=',
                DatesProvider::parseUTCToLocal($options['from'])->toDateTimeString()
            );
        }

        // until
        if (array_key_exists('until', $options)) {
            $until = Carbon::parse($options['until'], 'UTC');
            $until = $until->isStartOfDay() ? $until->addDay(1) : $until;
            $until = $until->setTimezone(Config::get('app.timezone'));
            if (array_key_exists('until', $options) && $options['until']) {
                $records = $records->where(
                    'updated_at', '<=',
                    $until->toDateTimeString()
                );
            }
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
        if ($dataSource = DataSource::find($record->registry_object_data_source_id)) {
            $oaiRecord
                ->addSet(new Set("datasource:". $dataSource->id))
                ->addSet(new Set("datasource:". $this->nameBackwardCompat($dataSource->title)));
        }

        if ($group = Group::where('title', $record->registry_object_group)->first()) {
            $oaiRecord
                ->addSet(new Set("group:".$group->id))
                ->addSet(new Set("group:".$this->nameBackwardCompat($group->title)));
            if ($this->groupNameBWCompat($group->title) != $this->nameBackwardCompat($group->title)) {
                $oaiRecord->addSet(new Set("group:" . $this->groupNameBWCompat($group->title)));
            }
        }

        return $oaiRecord;
    }

    private function addDCISets(Record $oaiRecord, DCI $record)
    {
        if ($dataSource = DataSource::find($record->registry_object_data_source_id)) {
            $oaiRecord
                ->addSet(new Set("datasource:". $dataSource->id))
                ->addSet(new Set("datasource:". $this->nameBackwardCompat($dataSource->title)));
        }

        if ($group = Group::where('title', $record->registry_object_group)->first()) {
            $oaiRecord
                ->addSet(new Set("group:".$group->id))
                ->addSet(new Set("group:".$this->nameBackwardCompat($group->title)));
            if ($this->groupNameBWCompat($group->title) != $this->nameBackwardCompat($group->title)) {
                $oaiRecord->addSet(new Set("group:" . $this->groupNameBWCompat($group->title)));
            }
        }

        return $oaiRecord;
    }

    private function listIdentifiersAltSchema($options)
    {
        $result = [];

        $records = $this->getAltSchemaVersions($options);

        foreach ($records['records'] as $record) {
            $oaiRecord = new Record(
                $record->key,
                Carbon::parse($record->updated_at)->setTimezone('UTC')->format($this->getDateFormat())
            );
            $oaiRecord = $this->addAltSchemaVersionsSets($oaiRecord, $record);
            $result[] = $oaiRecord;
        }

        return [
            'total' => $records['total'],
            'records' => $result,
            'limit' => $options['limit'],
            'offset' => $options['offset']
        ];
    }


    private function listAltSchemaVersions($options){
        $records = $this->getAltSchemaVersions($options);

        $result = [];

        foreach ($records['records'] as $record) {

            $oaiRecord = new Record(
                $record->key,
                Carbon::parse($record->updated_at)->setTimezone('UTC')->format($this->getDateFormat())
            );
            $oaiRecord = $this->addAltSchemaVersionsSets($oaiRecord, $record);
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

    /*
     *
     * for performance improvements dropping the usage of the AltChemaVersion View :-(
     * can't get it to work as fast as a query
     * need to read more
     *
     */
    private function getAltSchemaVersion($registry_object_key, $metadataPrefix)
    {

        $version =  Capsule::table('versions')->join('registry_object_versions', 'versions.id', '=' , 'registry_object_versions.version_id' )
            ->join('registry_objects', 'registry_object_versions.registry_object_id', '=', 'registry_objects.registry_object_id')
            ->join('schemas', 'versions.schema_id', '=', 'schemas.id')
            ->where('schemas.prefix', $metadataPrefix)
            ->where('schemas.exportable', 1)
            ->where('registry_objects.key', $registry_object_key)
            ->where('registry_objects.status', 'PUBLISHED')
            ->select('versions.data', 'versions.updated_at', 'registry_objects.key','registry_objects.group', 'registry_objects.registry_object_id', 'registry_objects.class', 'registry_objects.data_source_id')
            ->get();

        return $version;

    }

    /*
     *
     * for performance improvements dropping the usage of the AltChemaVersion View :-(
     * can't get it to work as fast as a query
     * need to read more
     *
     */
    private function getAltSchemaVersions($options)
    {

        $versions =  Capsule::table('versions')->join('registry_object_versions', 'versions.id', '=' , 'registry_object_versions.version_id' )
            ->join('registry_objects', 'registry_object_versions.registry_object_id', '=', 'registry_objects.registry_object_id')
            ->join('schemas', 'versions.schema_id', '=', 'schemas.id')
            ->where('schemas.prefix', $options['metadataPrefix'])
            ->where('schemas.exportable', 1)
            ->where('registry_objects.status', 'PUBLISHED');

        if ($options['set']) {
            $set = $options['set'];
            $set = explode(':', $set);

            $opt = $set[0];
            $value = $set[1];

            switch ($opt) {
                case "class":
                    $versions = $versions->where('registry_objects.class', $value);
                    break;
                case "datasource":
                    if ($value = $this->getDataSourceID($value)) {
                        $versions = $versions->where('registry_objects.data_source_id', $value);
                    }
                    break;
                case "group":
                    if ($value = $this->getGroupName($value)) {
                        $versions = $versions->where('registry_objects.group', $value);
                    }
                    break;
            }
        }

        // from
        if (array_key_exists('from', $options) && $options['from']) {
            $records = $versions->where(
                'versions.updated_at', '>=',
                DatesProvider::parseUTCToLocal($options['from'])->toDateTimeString()
            );
        }

        // until
        if (array_key_exists('until', $options)) {
            $until = Carbon::parse($options['until'], 'UTC');
            $until = $until->isStartOfDay() ? $until->addDay(1) : $until;
            $until = $until->setTimezone(Config::get('app.timezone'));
            if (array_key_exists('until', $options) && $options['until']) {
                $records = $records->where(
                    'versions.updated_at', '<=',
                    $until->toDateTimeString()
                );
            }
        }

        $count = $versions->count();
        $records = $versions->select('versions.data',
            'versions.updated_at',
            'registry_objects.key',
            'registry_objects.group',
            'registry_objects.registry_object_id',
            'registry_objects.class',
            'registry_objects.data_source_id')->limit($options['limit'])->offset($options['offset'])->get();

        return [
            'total' => $count,
            'records' => $records
        ];

    }


    private function addAltSchemaVersionsSets(Record $oaiRecord, $record)
    {

        $oaiRecord->addSet(new Set("class:".$record->class));

        if ($dataSource = DataSource::find($record->data_source_id)) {
            $oaiRecord
                ->addSet(new Set("datasource:". $dataSource->id))
                ->addSet(new Set("datasource:". $this->nameBackwardCompat($dataSource->title)));
        }

        if ($group = Group::where('title', $record->group)->first()) {
            $oaiRecord
                ->addSet(new Set("group:".$group->id))
                ->addSet(new Set("group:".$this->nameBackwardCompat($group->title)));
            if ($this->groupNameBWCompat($group->title) != $this->nameBackwardCompat($group->title)) {
                $oaiRecord->addSet(new Set("group:" . $this->groupNameBWCompat($group->title)));
            }
        }

        return $oaiRecord;
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        $formats = [];
        $schemas = \ANDS\Registry\Schema::where('exportable' , 1)->get();
        foreach ($schemas as $schema){
            $formats[$schema->prefix] = [
                'metadataPrefix' => $schema->prefix,
                'schema' => $schema->uri,
                'metadataNamespace' => $schema->uri
            ];
        }


       return array_merge($this->formats, $formats);
    }

    /**
     * @return array
     */
    public function getDefaultFormats()
    {
        return $this->formats;
    }

    private function getGroupID($name)
    {
        if ($group = Group::where('title', $name)->first()) {
            return $group->id;
        }

        return null;
    }
}