<?php


namespace ANDS\Registry\Suggestors;


use ANDS\RegistryObject;
use ANDS\Repository\RegistryObjectsRepository;
use Elasticsearch\ClientBuilder;

class UserDataSuggestor
{
    private $client = null;

    /**
     * UserDataSuggestor constructor.
     */
    public function __construct()
    {
        $url = env('ELASTICSEARCH_URL', 'http://localhost:9200');
        $url = rtrim($url, '/');

        $headers = @get_headers($url);
        if(!$headers || $headers[0] == 'HTTP/1.1 404 Not Found') {
            $this->client = null;
        } else {
            $this->client = ClientBuilder::create()
                ->setHosts(
                    [ $url ]
                )->build();
        }
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
        if ($this->client === null) {
            return [];
        }

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
//                        'minimum_should_match' => 1
                    ]
                ],
                'aggs' => [
                    'ip_viewed' => [
                        'terms' => [
                            'field' => 'doc.@fields.user.ip.raw',
                            'size' => 2147483647
                        ],
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
        } catch (\Exception $e) {
            // log error
            dd(get_exception_msg($e));
            monolog(self::class ." : ". get_exception_msg($e), "error", "error");
            return [];
        }

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
                            ['match' => ['doc.@fields.event' => 'portal_view']],
                            ['match' => ['doc.@fields.record.class' => 'collection']],
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
                                'terms' => [
                                    'field' => 'doc.@fields.record.key.raw',
                                    'size' => 100
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
//dd(count($sorted));

        // get first 100
        $sorted = $sorted->take(100);

        // filter out the ones that actually exists
        $ids = $sorted->keys()->toArray();
        $exists = RegistryObject::whereIn('key', $ids)->where('status', 'PUBLISHED')->get();

        // format
        $result = [];
        foreach ($exists as $record) {
            $result[] = [
                'id' => $record->id,
                'title' => $record->title,
                'key' => $record->key,
                'slug' => $record->slug,
                'RDAUrl' => baseUrl($record->slug. '/'. $record->id),
                'score' => $sorted->get($record->key)
            ];
        }

        // normalise
        $highest = collect($result)->pluck('score')->max();

        $result = collect($result)->map(function($item) use ($highest){
            $item['score'] = round($item['score'] / $highest, 5);
            return $item;
        });

        $result = $result->sortBy('score')->reverse();

        $result = array_values($result->toArray());

        return $result;
    }
}