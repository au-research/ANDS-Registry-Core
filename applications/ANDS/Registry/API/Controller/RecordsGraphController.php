<?php


namespace ANDS\Registry\API\Controller;

use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Mycelium\RelationshipSearchService;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
use ANDS\Util\RelationUtil;
use ANDS\Util\StrUtil;

class RecordsGraphController
{

    /**
     * Normalise the relationships to only display 1 way when any of these relationships are encountered
     *
     * @var array
     * */
    public static $flippableRelation = [
        'addsValueTo' => 'hasValueAddedBy',
        'describes' => 'isDescribedBy',
        'enriches' => 'isEnrichedBy',
        'hasCollector' => 'isCollectorOf',
        'hasDerivedCollection' => 'isDerivedFrom',
        'hasMember' => 'isMemberOf',
        'hasOutput' => 'isOutputOf',
        'hasPart' => 'isPartOf',
        'hasParticipant' => 'isParticipantIn',
        'hasPrincipalInvestigator' => 'isPrincipalInvestigatorOf',
        'isFunderOf' => 'isFundedBy',
        'makesAvailable' => 'isAvailableThrough',
        'operatesOn' => 'isOperatedOnBy',
        'presents' => 'isPresentedBy',
        'produces' => 'isProducedBy',
        'supports' => 'isSupportedBy',
        'isLocationFor' => 'isLocatedIn',
        'isManagerOf' => 'isManagedBy',
        'isOwnerOf' => 'isOwnedBy',
        'funds' => 'isFundedBy',
        'outputs' => 'isOutputOf',
    ];

    // only the following vertices labels are included in the visualisation
    public static $visualisableLabels = [
        "collection", "activity", "party", "service", "publication", "website"
    ];

    /**
     * Serves /api/registry/records/:id/graph
     *
     * todo caching
     * @param $id
     * @return array
     */
    public function index($id)
    {
        header("Access-Control-Allow-Origin: *");
        return $this->getGraphVisualisationForRecord($id);

        //        $disableCache = !! Request::get('cache');
        //        if (!$disableCache) {
        //            // caches by default
        //            // R28: does not accept custom parameters yet
        //            return Cache::driver('graph')->rememberForever("graph.$id", function() use ($id){
        //                return $this->getGraphVisualisationForRecord($id);
        //            });
        //        }
        //
        //        return $this->getGraphVisualisationForRecord($id);
    }

    /**
     * get a graph for any given Identifier Node
     * eg /api/registry/records/by_identifier/graph?identifier=10.5555/AIRHEWI3&identifier_type=doi&class=publication
     * @param $method
     * @param $type
     * @param $query
     * @return array|\array[][][][][]
     */
    public function index_identifier($method, $type, $query)
    {

        parse_str($query, $params);

        // obtain the graph data from MyceliumService
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
        $graphResult = $myceliumClient->getIdentifierGraph($params['identifier'], $params['identifier_type']);
        if ($graphResult === null || $graphResult->getStatusCode() != 200) {
            // todo log mycelium errors
            return $this->formatForJSLibrary([], []);
        }
        $graphContent = json_decode($graphResult->getBody()->getContents(), true);

        return $this->formatGrapResult($graphContent, $params['identifier'], $params['class']);
    }


    public function getGraphVisualisationForRecord($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);

        if (!$record) {
            return $this->formatForJSLibrary([], []);
        }
        // obtain the graph data from MyceliumService
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
        // TODO
        // temporary fallback to published relationships if exists
        // remove switch once DRAFT records are indexed by Mycelium

        if(!$record->isPublishedStatus()){
            $publishedRecord = RegistryObjectsRepository::getPublishedByKey($record->key);
            if($publishedRecord != null){
                $graphResult = $myceliumClient->getRecordGraph($publishedRecord->id);
            }
        }else{
            $graphResult = $myceliumClient->getRecordGraph($record->id);
        }

        if ($graphResult === null || $graphResult->getStatusCode() != 200) {
            // todo log mycelium errors
            return $this->formatForJSLibrary([], []);
        }
        $graphContent = json_decode($graphResult->getBody()->getContents(), true);

        return $this->formatGrapResult($graphContent, $record->id, $record->class);
    }

    /**
     * @param $graphContent
     * @return array|\array[][][][][]
     */
    private function formatGrapResult($graphContent, $from_id, $from_class)
    {
        // format the nodes
        $nodes = collect($graphContent['vertices'])
            ->map(function ($vertex) {
                $labels = $vertex['labels'];

                // RegistryObject, cluster label have to be the first label for highlighting purpose
                if (in_array('Cluster', $vertex['labels'])) {
                    $labels = ['cluster'];
                } else {
                    if (in_array('RegistryObject', $vertex['labels'])) {
                        $labels = ['RegistryObject'];
                    }
                }

                $labels[] = $vertex['objectClass'];
                $labels[] = $vertex['objectType'];
                return [
                    'id' => $vertex['id'],
                    'labels' => $labels,
                    'properties' => [
                        'url' => $vertex['url'],
                        'title' => $vertex['title'],
                        'roId' => $vertex['identifier'],
                        'identifier' => $vertex['identifier'],
                        'identifier_type' => $vertex['identifierType'],
                        'class' => $vertex['objectClass'],
                        'type' => $vertex['objectType']
                    ]
                ];
            })->filter(function ($vertex) {
                // exclude the vertices that are not visualise-able
                return count(array_intersect($vertex['labels'], static::$visualisableLabels)) >= 1;
            })->toArray();

        // collect a list of visualise-able vertices ID for easier comparison
        $validNodeIDs = collect($nodes)->pluck('id')->toArray();

        // format the edges
        $edges = collect($graphContent['edges'])->map(function ($edge) use ($from_id) {
            return [
                'id' => $edge['id'],
                'from' => $edge['from'],
                'to' => $edge['to'],
                'startNode' => $edge['from']['id'],
                'endNode' => $edge['to']['id'],
                'type' => $edge['type']
            ];
        })->filter(function($edge) use ($validNodeIDs){
            // only include the edges that connect to or from the visualise-able vertices
            return in_array($edge['from']['id'], $validNodeIDs) && in_array($edge['to']['id'], $validNodeIDs);
        })->toArray();

        // format the cluster nodes
        $nodes = collect($nodes)->map(function ($node) use ($from_id, $edges) {
            if (!in_array('cluster', $node['labels'])) {
                return $node;
            }

            // add RelatedInfo to labels if it's not a RegistryObject cluster
            if (!in_array('RegistryObject', $node['labels'])) {
                $node['labels'][] = 'RelatedInfo';
            }

            $node['properties']['clusterClass'] = $node['properties']['class'];

            $edgeToCluster = collect($edges)->filter(function ($edge) use ($node) {
                return $edge['endNode'] === $node['id'];
            })->first();

            $count = 0;
            if ($edgeToCluster) {
                // populate count from SOLR
                $result = RelationshipSearchService::search([
                    'from_id' => $from_id,
                    'relation_type' => $edgeToCluster['type'],
                    'to_class' => $node['properties']['class'],
                    'to_type' => escapeSolrValue($node['properties']['type'])
                ], ['rows' => 0]);
                $count = $result->total;
            }

            $node['properties']['count'] = $count;

            // todo populate portalSearchUrl
//            $node['properties']['url'] = null;
            $node['properties']['url'] = constructPortalSearchQuery([
                'related_object_id' => $from_id,
                'class' => $node['properties']['class'],
                'type' => $node['properties']['type'],
                'relation' => $edgeToCluster['type']
            ]);

            return $node;
        });

        $edges = $this->flipEdges($edges);
        $edges = $this->dedupeDirectEdges($edges);
        $edges = $this->dedupeReversedEdges($edges);
        $edges = $this->formatEdges($edges, $from_class);
        $edges = $this->cleanEdges($edges);

        return $this->formatForJSLibrary($nodes, $edges);
    }

    /**
     * Merge the edge, when there are multiple edges going from a node to another
     *
     * @param $edges
     * @return \Illuminate\Support\Collection
     */
    public function dedupeDirectEdges($edges)
    {
        $edges = collect($edges)
            ->map(function ($link) use ($edges) {
                $types = collect($edges)
                    ->filter(function ($link2) use ($link) {
                        return $link2['startNode'] === $link['startNode'] && $link2['endNode'] === $link['endNode'];
                    })->pluck('type')->unique()->toArray();
                $link['type'] = count($types) > 1 ? 'multiple' : $link['type'];
                $link['multiple'] = count($types) > 1;
                $link['properties']['types'] = $types;
                return $link;
            });

        // unique the relationships by start and end node id
        // all reverse links flipping and merging should be done by this point
        return collect($edges)
            ->unique(function ($link) {
                return $link['startNode'] . $link['endNode'];
            })->map(function ($edge) {
                $edge['id'] = $edge['startNode'] . $edge['endNode'];
                return $edge;
            });
    }

    /**
     * Merge the edge, when there are multiple edges going between a node to another in diferent direction
     *
     * @param $edges
     * @return array
     */
    public function dedupeReversedEdges($edges)
    {
        $edgeIDsToRemove = [];
        $edges = collect($edges)
            ->map(function ($edge) use (&$edges, &$edgeIDsToRemove) {

                // skip checking if this edge is already marked for removal
                if (in_array($edge['id'], $edgeIDsToRemove)){
                    return $edge;
                }

                // find the reversed edge
                $reversedEdges = collect($edges)->filter(function ($edge2) use ($edge) {
                    return !isset($edge['checked'])
                        && $edge['multiple'] === false
                        && $edge['startNode'] === $edge2['endNode']
                        && $edge['endNode'] === $edge2['startNode'];
                });
                if (!$reversedEdges->isEmpty()) {
                    $edgeIDsToRemove = array_merge($edgeIDsToRemove, $reversedEdges->pluck('id')->toArray());
                    $types = collect($edge['properties']['types'])
                        ->merge($reversedEdges->pluck('properties.types')->flatten())
                        ->unique()
                        ->map(function($type) {
                            return RelationUtil::getReverse($type);
                        });
                    $edge['properties']['types'] = $types->toArray();
                    $edge['multiple'] = $types->count() > 1;
                    $edge['type'] = $types->count() > 1 ? 'multiple' : $edge['type'];
                    $edge['checked'] = true;
                }
                return $edge;
            });

        // remove reversed edges
        $edges = collect($edges)->filter(function ($edge) use ($edgeIDsToRemove) {
            return !in_array($edge['id'], $edgeIDsToRemove);
        });

        return $edges->toArray();
    }

    /**
     * If an edge is flippable, flip it to the right orientation.
     * Flippability is determined in the pre-determined flipptableRelation array
     * @param $edges
     * @return \Illuminate\Support\Collection
     */
    public function flipEdges($edges)
    {
        $flippableRelation = self::$flippableRelation;
        return collect($edges)
            ->map(function ($edge) use ($flippableRelation) {
                if (array_key_exists($edge['type'], $flippableRelation)) {
                    return [
                        'id' => $edge['id'],
                        'from' => $edge['to'],
                        'to' => $edge['from'],
                        'startNode' => $edge['to']['id'],
                        'endNode' => $edge['from']['id'],
                        'type' => $flippableRelation[$edge['type']]
                    ];
                }
                return $edge;
            });
    }

    /**
     * Change the type and add the html value of the edge for human readability
     *
     * @param $edges
     * @param $record
     * @return \Illuminate\Support\Collection
     */
    public function formatEdges($edges, $from_class)
    {
        return collect($edges)->map(function ($edge) use ($from_class) {

            $fromIcon = StrUtil::portalIconHTML($edge['from']['objectClass'], $edge['from']['objectType']);
            $toIcon = StrUtil::portalIconHTML($edge['to']['objectClass'], $edge['to']['objectType']);

            $relation = $edge['type'];
            $relationType = RelationUtil::contains($relation)
                ? RelationUtil::getDisplayText($relation, $from_class) : $relation;

            $edge['type'] = $relationType;
            $edge['html'] = "$fromIcon $relationType $toIcon";

            if (array_key_exists('multiple', $edge)) {
                $edge['html'] = '';
                foreach ($edge['properties']['types'] as $type) {
                    $relationType = RelationUtil::contains($type)
                        ? RelationUtil::getDisplayText($type, $from_class) : $type;
                    $edge['html'] .= "$fromIcon $relationType $toIcon<br/>";
                }
            }

            return $edge;
        });
    }

    /**
     * Remove unnecessary information from the edges to clean up the data
     *
     * @param $edges
     * @return array
     */
    public function cleanEdges($edges)
    {
        return collect($edges)
            ->map(function ($edge) {
                unset($edge['from']);
                unset($edge['to']);
                unset($edge['multiple']);
                return $edge;
            })->values()->toArray();
    }

    /**
     * Format for neo4jd3 library
     *
     * @param $nodes
     * @param $relationships
     * @return array
     */
    public function formatForJSLibrary($nodes, $relationships)
    {
        return [
            'results' => [
                [
                    'data' => [
                        [
                            'graph' => [
                                'nodes' => collect($nodes)->values()->toArray(),
                                'relationships' => collect($relationships)->values()->toArray()
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}