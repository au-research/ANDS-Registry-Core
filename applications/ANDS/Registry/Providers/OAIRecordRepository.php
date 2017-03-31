<?php


namespace ANDS\Registry\Providers;


use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\RegistryObject;
use ANDS\RegistryObjectAttribute;
use ANDS\Util\XMLUtil;
use Carbon\Carbon;
use MinhD\OAIPMH\Interfaces\OAIRepository;
use MinhD\OAIPMH\Record;
use MinhD\OAIPMH\Set;

class OAIRecordRepository implements OAIRepository
{
    public $dateFormat = "Y-m-d\\Th:m:s\\Z";

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
            ]
        ];
    }

    public function listRecords($metadataFormat = null, $set = null, $options)
    {
        // TODO Set
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

        $result = [];
        foreach ($records as $record) {
            $oaiRecord = new Record(
                "oai:ands.org.au:{$record->id}",
                DatesProvider::getCreatedDate($record, $this->getDateFormat())
            );

            // set
            $oaiRecord->addSet(new Set("class:{$record->class}", $record->class));

            // metadata TODO metadataPrefix
            $metadata = "<registryObject />";
            $recordMetadata = MetadataProvider::getSelective($record, ['recordData']);
            if (array_key_exists('recordData', $recordMetadata)) {
                $metadata = XMLUtil::unwrapRegistryObject($recordMetadata['recordData']);
            }
            $oaiRecord->setMetadata($metadata);

            $result[] = $oaiRecord;
        }

        return [
            'total' => $total,
            'records' => $result,
            'limit' => $limit,
            'offset' => $offset
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
        // TODO: Implement getRecord() method.
    }

    public function listRecordsByToken($token)
    {
        // TODO: Implement listRecordsByToken() method.
    }

    public function listIdentifiers($metadataPrefix = null)
    {
        // TODO: Implement listIdentifiers() method.
    }
}