<?php


namespace ANDS\Registry\Suggestors;


use ANDS\RegistryObject;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;

class UserDataSuggestor implements RegistryObjectSuggestor
{
    private $client = null;

    protected static $ipLimitCount = 200;
    protected static $recordLimitCount = 100;

    /**
     * UserDataSuggestor constructor.
     */
    public function __construct()
    {
        $url = \ANDS\Util\Config::get('app.elasticsearch_url');

        $url = rtrim($url, '/');

        // check if headers are available with the timeout of 2s
        // this occurs when ElasticSearch is not reachable, and caused a stall in a lot of operation
        $opts['http']['timeout'] = 2;
        $defaultOptions = stream_context_get_options(stream_context_get_default());
        stream_context_set_default($opts);
        $headers = @get_headers($url);
        stream_context_set_default($defaultOptions);

        if(!$headers || $headers[0] == 'HTTP/1.1 404 Not Found') {
            $this->client = null;
        } else {
            $this->client = ClientBuilder::create()
                ->setHosts(
                    [ $url ]
                )->build();
        }
    }

    public function isClientOnline()
    {
        return $this->client !== null;
    }

    /**
     * TODO cache results
     *
     * @param RegistryObject $record
     * @return array|null
     */
    public function suggest(RegistryObject $record)
    {
        return $this->suggestByView($record);
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

                            // last 12 months
                            [
                                'range' => [
                                'doc.@timestamp' =>
                                    [
                                        'gte' => Carbon::now()->addYear(-1)->timestamp,
                                        'lte' => Carbon::now()->timestamp,
                                        'format' => 'epoch_second'
                                    ]
                                ]
                            ]
                        ],
                    ]
                ],
                'aggs' => [
                    'ip_viewed' => [
                        'terms' => [
                            'field' => 'doc.@fields.user.ip.raw',
                            'size' => self::$ipLimitCount
                        ],
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
        } catch (\Exception $e) {
            // log error
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
                                    'size' => self::$recordLimitCount
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
        } catch (\Exception $e) {
            // log error
            monolog(self::class ." : ". get_exception_msg($e), "error", "error");
            return [];
        }

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

        // get first 100
        $sorted = $sorted->take(self::$recordLimitCount);

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
                'RDAUrl' => $record->portalUrl,
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