<?php


namespace ANDS\Registry\Suggestors;


use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use Elasticsearch\ClientBuilder;

class UserDataSuggestor
{
    private $client;

    /**
     * UserDataSuggestor constructor.
     */
    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(
                [ env('ELASTICSEARCH_URL') ]
            )->build();
    }

    /**
     * find all the IPs that view the record
     * find all the Records that those IPs view
     * rank those records and return them
     * @param RegistryObject $record
     * @return array
     */
    public function suggestByView(RegistryObject $record)
    {
        // find all IPs that view this record,
        // TODO except current IP

        $id = $record->id;

        $params = [
            'index' => 'portal-*',
            'type' => 'portal',
            'body' => [
                'from' => 0, 'size' => 0,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['doc.@fields.record.id' => $id]],
                            ['match' => ['doc.@fields.event' => 'portal_view']],
                        ],
                        'minimum_should_match' => 1
                    ]
                ],
                'aggs' => [
                    'ip_viewed' => [
                        'terms' => [
                            'field' => 'doc.@fields.user.ip.raw'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->search($params);

        // get all the ips that view this record
        $ips = collect($response['aggregations']['ip_viewed']['buckets'])->pluck('key')->toArray();

        // find all records that these ip viewed
        $shouldMatchIps = collect($ips)->map(function($ip) {
            return [
                'match' => ['doc.@fields.user.ip' => $ip]
            ];
        });

        $params = [
            'index' => 'portal-*', 'type' => 'portal',
            'body' => [
                'from' => 0, 'size' => 0,
                'query' => [
                    'bool' => [
                        'should' => $shouldMatchIps,
                        'must' => [
                            'match' => [ 'doc.@fields.event' => 'portal_view' ]
                        ],
                        'must_not' => [
                            'match' => ['doc.@fields.record.id' => $id]
                        ],
                        'minimum_should_match' => 1
                    ]
                ],
                'aggs' => [
                    'ip_viewed' => [
                        'terms' => [
                            'field' => 'doc.@fields.user.ip.raw'
                        ],
                        'aggs' => [
                            'records_viewed' => [
                                'significant_terms' => [
                                    'field' => 'doc.@fields.record.id.raw'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->search($params);

        $recordsViewedPerUser = collect($response['aggregations']['ip_viewed']['buckets'])->pluck('records_viewed')->pluck('buckets');

        $recordOccurences = [];
        foreach ($recordsViewedPerUser as $stats) {
            $keys = collect($stats)->pluck('key');
            foreach ($keys as $key) {
                if (array_key_exists($key, $recordOccurences)) {
                    $recordOccurences[$key]++;
                } else {
                    $recordOccurences[$key] = 1;
                }
            }
        }

        // sort
        $sorted = collect($recordOccurences)->sort()->reverse();

        // format
        $result = [];
        foreach ($sorted as $id => $score) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            if (!$record) {
                continue;
            }
            $result[] = [
                'id' => $record->id,
                'title' => $record->title,
                'key' => $record->key,
                'slug' => $record->slug,
                'RDAUrl' => baseUrl($record->slug. '/'. $record->id),
                'score' => $score
            ];
        }

        return $result;
    }
}