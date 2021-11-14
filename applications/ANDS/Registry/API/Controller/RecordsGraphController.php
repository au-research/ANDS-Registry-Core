<?php


namespace ANDS\Registry\API\Controller;


use ANDS\Cache\Cache;
use ANDS\Mycelium\MyceliumServiceClient;
use ANDS\Mycelium\RelationshipSearchService;
use ANDS\Registry\API\Request;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
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

    /**
     * Serves /api/registry/records/:id/graph
     *
     * todo caching
     * @param $id
     * @return array
     */
    public function index($id)
    {

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

    public function getGraphVisualisationForRecord($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);

        if (!$record) {
            return $this->formatForJSLibrary([], []);
        }

        // obtain the graph data from MyceliumService
        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
        $graphResult = $myceliumClient->getRecordGraph($record->id);
        if ($graphResult->getStatusCode() != 200) {
            // todo log mycelium errors
            return $this->formatForJSLibrary([], []);
        }
        $graphContent = json_decode($graphResult->getBody()->getContents(), true);

        // format the nodes
        $nodes = collect($graphContent['vertices'])->map(function ($vertex) {
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
                    'class' => $vertex['objectClass'],
                    'type' => $vertex['objectType']
                ]
            ];
        })->toArray();

        // format the edges
        $edges = collect($graphContent['edges'])->map(function ($edge) use ($record) {
            return [
                'id' => $edge['id'],
                'from' => $edge['from'],
                'to' => $edge['to'],
                'startNode' => $edge['from']['id'],
                'endNode' => $edge['to']['id'],
                'type' => $edge['type']
            ];
        })->toArray();

        // format the cluster nodes
        $nodes = collect($nodes)->map(function ($node) use ($record, $edges) {
            if (!in_array('cluster', $node['labels'])) {
                return $node;
            }

            $node['properties']['clusterClass'] = $node['properties']['class'];

            $edgeToCluster = collect($edges)->filter(function ($edge) use ($node) {
                return $edge['endNode'] === $node['id'];
            })->first();

            $count = 0;
            if ($edgeToCluster) {
                // populate count from SOLR
                $result = RelationshipSearchService::search([
                    'from_id' => $record->id,
                    'relation_type' => $edgeToCluster['type'],
                    'to_class' => $node['properties']['class'],
                    'to_type' => $node['properties']['type']
                ], ['rows' => 0]);
                $count = $result->total;
            }

            $node['properties']['count'] = $count;

            // todo populate portalSearchUrl
            $node['properties']['url'] = baseUrl();

            return $node;
        });

        $edges = $this->flipEdges($edges);
        $edges = $this->dedupeDirectEdges($edges);
        $edges = $this->dedupeReversedEdges($edges);
        $edges = $this->formatEdges($edges, $record);
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
                $reversedEdges = collect($edges)->filter(function ($edge2) use ($edge) {
                    return !isset($edge['checked'])
                        && $edge['multiple'] === false
                        && $edge['startNode'] === $edge2['endNode']
                        && $edge['endNode'] === $edge2['startNode'];
                });
                if (!$reversedEdges->isEmpty()) {
                    $edgeIDsToRemove = array_merge($edgeIDsToRemove, $reversedEdges->pluck('id')->toArray());
                    $types = collect($edge['properties']['types'])
                        ->merge($reversedEdges->pluck('properties.types')->flatten())->unique();
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
    public function formatEdges($edges, $record)
    {
        return collect($edges)->map(function ($edge) use ($record) {

            $fromIcon = StrUtil::portalIconHTML($edge['from']['objectClass'], $edge['from']['objectType']);
            $toIcon = StrUtil::portalIconHTML($edge['to']['objectClass'], $edge['to']['objectType']);

            $relation = $edge['type'];
            $relationType = RelationUtil::contains($relation)
                ? RelationUtil::getDisplayText($relation, $record->class) : $relation;

            $edge['type'] = $relationType;
            $edge['html'] = "$fromIcon $relationType $toIcon";

            if (array_key_exists('multiple', $edge)) {
                $edge['html'] = '';
                foreach ($edge['properties']['types'] as $type) {
                    $relationType = RelationUtil::contains($type)
                        ? RelationUtil::getDisplayText($type, $record->class) : $type;
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
                                'nodes' => $nodes,
                                'relationships' => $relationships
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}