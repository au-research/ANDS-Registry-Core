<?php


namespace ANDS\Registry\API\Controller;


use ANDS\Cache\Cache;
use ANDS\Mycelium\MyceliumServiceClient;
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
     * normalise the relationships to only display 1 way when any of these relationships are encountered
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
     * api/registry/records/:id/graph
     * @param $id
     * @return mixed|null
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

    public function getGraphVisualisationForRecord($id) {
        $record = RegistryObjectsRepository::getRecordByID($id);

        if (!$record) {
            return $this->formatForJSLibrary([], []);
        }

        // todo clusters
        /**
         * Find out how many relationType counts are via a SOLR nested document facet using JSON Facet API
         * call Mycelium Client and exclude the relationType that is over the limit
         * create from-[relationType]->(cluster) for all the relationType that is over the limit
         */

        $myceliumClient = new MyceliumServiceClient(Config::get('mycelium.url'));
        $graphResult = $myceliumClient->getRecordGraph($record->id);
        if ($graphResult->getStatusCode() != 200) {
            // todo log errors
            return $this->formatForJSLibrary([], []);
        }
        $graphContent = json_decode($graphResult->getBody()->getContents(), true);

        // format the nodes
        $nodes = collect($graphContent['vertices'])->map(function ($vertex) {
            $labels = $vertex['labels'];

            // RegistryObject label have to be the first label for highlighting purpose
            if (in_array('RegistryObject', $vertex['labels'])) {
                $labels = ['RegistryObject'];
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
        $edges = collect($graphContent['edges'])->map(function ($edge) use($record){

            return [
                'id' => $edge['id'],
                'from' => $edge['from'],
                'to' => $edge['to'],
                'startNode' => $edge['from']['id'],
                'endNode' => $edge['to']['id'],
                'type' => $edge['type']
            ];
        })->toArray();

        // flip edges
        $flippableRelation = self::$flippableRelation;
        $edges = collect($edges)
            ->map(function($edge) use ($flippableRelation) {
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

        // deduplicate
        $edges = collect($edges)
            ->map(function($link) use ($edges){
                $types = collect($edges)
                    ->filter(function($link2) use ($link){
                        return $link2['startNode'] === $link['startNode'] && $link2['endNode'] === $link['endNode'];
                    })->pluck('type')->unique()->toArray();
                $link['type'] = count($types) > 1 ? 'multiple' : $link['type'];
                $link['multiple'] = count($types) > 1;
                $link['properties']['types'] = $types;
                return $link;
            });



        // unique the relationships by start and end node id
        // all reverse links flipping and merging should be done by this point
        $edges = collect($edges)
            ->unique(function($link){
                return $link['startNode'].$link['endNode'];
            })->map(function($edge){
                $edge['id'] = $edge['startNode'].$edge['endNode'];
                return $edge;
            });

        // find edges that can be merged
        $edgeIDsToRemove = [];
        $edges = collect($edges)
            ->map(function($edge) use ($edges, &$edgeIDsToRemove) {
                $reversedEdges = collect($edges)->filter(function($edge2) use ($edge){
                    return $edge['multiple'] === false && $edge['startNode'] === $edge2['endNode'] && $edge['endNode'] === $edge2['startNode'];
                });
                if (! $reversedEdges->isEmpty()) {
                    $edgeIDsToRemove = array_merge($edgeIDsToRemove, $reversedEdges->pluck('id')->toArray());
                    $types = collect($edge['properties']['types'])
                        ->merge($reversedEdges->pluck('properties.types')->flatten());
                    $edge['properties']['types'] = $types->unique()->toArray();
                    $edge['multiple'] = $types->count() > 1;
                    $edge['type'] = $types->count() > 1 ? 'multiple' : $edge['type'];
                }
                return $edge;
            });

        // remove reversed edges
        $edges = collect($edges)->filter(function($edge) use ($edgeIDsToRemove) {
            return !in_array($edge['id'], $edgeIDsToRemove);
        });

        // relationType text value and html value
        $edges = collect($edges)->map(function ($edge) use($record){

            $fromIcon = StrUtil::portalIconHTML($edge['from']['objectClass'], $edge['from']['objectType']);
            $toIcon = StrUtil::portalIconHTML($edge['to']['objectClass'], $edge['to']['objectType']);

            $relation = $edge['type'];
            $relationType = RelationUtil::contains($relation)
                ? RelationUtil::getDisplayText($relation, $record->class) : $relation;

            $edge['type'] = $relationType;
            $edge['html'] = "$fromIcon $relationType $toIcon";

            if (array_key_exists('multiple', $edge)) {
                $edge['html'] = '';
                foreach ($edge['properties']['types'] as $type){
                    $relationType = RelationUtil::contains($type)
                        ? RelationUtil::getDisplayText($type, $record->class) : $type;
                    $edge['html'] .= "$fromIcon $relationType $toIcon<br/>";
                }
            }

            return $edge;
        });

        // unset unneeded properties to make the graph cleaner
        $edges = collect($edges)
            ->map(function($edge) {
                unset($edge['from']);
                unset($edge['to']);
                unset($edge['multiple']);
                return $edge;
            })->values()->toArray();

        return $this->formatForJSLibrary($nodes, $edges);
    }

    /**
     * TODO: Refactor to GraphRelationships formatForPortal?
     * TODO: accepts parameters for different options
     * TODO: fix constructPortalSearchQuery to not use CI SOLR
     * TODO: fix getSolrCountForFilter to not use CI SOLR
     *
     * @param $id
     * @return array
     */
    public function getGraphForRecord($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);

        if (!$record) {
            return $this->formatForJSLibrary([], []);
        }

        $graph = GraphRelationshipProvider::getByID($id);

        $nodes = array_values($graph['nodes']);
        $relationships = array_values($graph['links']);

        /**
         * Format the RegistryObject clusters
         * add search url, get counts from SOLR
         * add a bunch of meta
         */
        $clusters = collect($nodes)
            ->filter(function ($item) {
                // only look at RegistryObject cluster
                return in_array("cluster", $item['labels']) && !in_array("RelatedInfo", $item['labels']);
            })->map(function ($cluster) use ($relationships, $record) {

                $clusterClass = $cluster['properties']['class'];
                $clusterType = $cluster['properties']['type'];

                // related_party_multi_id construction
                $searchClass = $record->class;
                if ($record->class == 'party') {
                    $searchClass = (strtolower($record->type) == 'group') ? 'party_multi' : 'party_one';
                }

                // find the relationship that connects this cluster to the current node
                $relation = collect($relationships)->filter(function ($rel) use ($cluster) {
                    return $rel['endNode'] === $cluster['id'];
                })->first();

                $relationType = $relation['type'];

                if (in_array($relationType, array_keys(GraphRelationshipProvider::$flippableRelation))) {
                    $relationType = array_search($relationType, GraphRelationshipProvider::$flippableRelation[$relationType]);
                }

                $filters = [
                    'class' => $clusterClass,
                    'type' => $clusterType,
                    "related_{$searchClass}_id" => $record->id,
                    'relation' => $relationType
                ];

                $count = getSolrCountForQuery($filters);

                // if the count is not correct, try flip the relation and get the count again
                // TODO real fix is to determine if the relation has been flipped, only flip if it has been flipped
                if ($count === 0 && in_array($relationType, array_values(GraphRelationshipProvider::$flippableRelation))) {
                    $relationType = array_search($relationType, GraphRelationshipProvider::$flippableRelation);
                    $filters = [
                        'class' => $clusterClass,
                        'type' => $clusterType,
                        "related_{$searchClass}_id" => $record->id,
                        'relation' => $relationType
                    ];
                    $count = getSolrCountForQuery($filters);
                }

                $classPlural = StrUtil::plural($clusterClass);
                if($clusterType == 'software') $classPlural = "software records";
                $cluster['properties'] = array_merge($cluster['properties'], [
                    'title' => "$count related $classPlural",
                    'url' => constructPortalSearchQuery($filters),
                    'count' => $count,
                    'class' => $clusterClass,
                    'clusterClass' => $clusterClass
                ]);

                return $cluster;
            });

        $nodes = collect($nodes)
            ->map(function ($node) use ($clusters) {
                // is not a cluster, do nothing
                if (!in_array($node['id'], $clusters->pluck('id')->toArray(), true)) {
                    return $node;
                }

                // Add the cluster information from the provided $clusters (above)
                return $clusters->filter(function ($c) use ($node) {
                    return $c['id'] == $node['id'];
                })->first();
            })->map(function($node) {
                // Formats the node and provide title and url
                $props = $node['properties'];

                // has a slug, this means it's a registryObject, view url provided
                if (array_key_exists('slug', $props)) {
                    $node['properties']['url'] = baseUrl($node['properties']['slug'].'/'.$node['properties']['roId']);
                    return $node;
                }

                // has an identifier and identifierType, formats the href based on IdentifierProvider::format functions
                if (array_key_exists('identifier', $props) && array_key_exists('identifierType', $props)) {
                    $identifier = IdentifierProvider::format($props['identifier'], $props['identifierType']);
                    if ($identifier && array_key_exists('href', $identifier)){
                        $node['properties']['url'] = $identifier['href'];
                    }
                    if (!array_key_exists('title', $props)) {
                        $node['properties']['title'] = $props['identifier'];
                    }
                    return $node;
                }

                // similar to above, but checks the type field of the props instead
                // this is in case the type is used instead of identifierType (the case for some relatedInfo)
                if (array_key_exists('identifier', $props) && array_key_exists('type', $props)) {
                    $identifier = IdentifierProvider::format($props['identifier'], $props['type']);
                    if ($identifier && array_key_exists('href', $identifier)){
                        $node['properties']['url'] = $identifier['href'];
                    }
                    if (!array_key_exists('title', $props)) {
                        $node['properties']['title'] = $props['identifier'];
                    }
                    return $node;
                }

                return $node;
            })->values()->toArray();

        // deduplication
        $relationships = collect($relationships)
            ->map(function($link) use ($relationships){
                // count the number of times this link has happened
                $link['count'] = collect($relationships)->filter(function($link2) use ($link){
                    return $link2['startNode'] === $link['startNode'] && $link2['endNode'] === $link['endNode'];
                })->count();
                return $link;
            })->map(function($link) use ($relationships){
                // stores link types and multiple status (for later use)

                // this is a duplicate link
                if ($link['count'] > 1) {
                    $types = collect($relationships)
                        ->filter(function($link2) use ($link){
                            return $link2['startNode'] === $link['startNode'] && $link2['endNode'] === $link['endNode'];
                        })->pluck('type')->unique()->toArray();
                    $link['type'] = 'multiple';
                    $link['multiple'] = true;
                    $link['properties']['types'] = $types;
                    return $link;
                }

                // not duplicate link
                $link['multiple'] = false;
                $link['properties']['types'] = [ $link['type'] ];
                return $link;
            });

        // reverse relationship after being merged into a (priority) list will be removed
        $toRemove = [];

        // reverse
        $relationships = collect($relationships)
            ->sortByDesc('count')
            ->map(function ($link) use ($relationships) {
                // find reverse link and store it within this link (for later use)
                $link['reverse'] = collect($relationships)
                        ->filter(function ($link2) use ($link) {
                        return $link2['startNode'] === $link['endNode'] && $link2['endNode'] === $link['startNode'];
                    });
                return $link;
            })->map(function ($link) use ($relationships, &$toRemove) {
                // add the reverse link to the $toRemove array if sensible
                // TODO: simplify logic
                if (count($link['reverse']) > 0 && !in_array($link['id'], $toRemove)) {
                    foreach ($link['reverse'] as $reverse) {
                        if ($reverse['type'] === 'multiple') {
                            foreach ($reverse['properties']['types'] as $reverseType) {
                                $link['properties']['types'][] = getReverseRelationshipString($reverseType);
                            }
                        } else {
                            $link['properties']['types'][] = getReverseRelationshipString($reverse['type']);
                        }
                        $link['properties']['types'] = array_unique($link['properties']['types']);

                        $toRemove[] = $reverse['id'];
                    }
                }
                return $link;
            })->filter(function ($link) use ($toRemove) {
                // remove the link if it's been marked
                return !in_array($link['id'], $toRemove);
            });

        // unique the relationships by start and end node id
        // all reverse links flipping and merging should be done by this point
        $relationships = collect($relationships)->unique(function($link){
            return $link['startNode'].$link['endNode'];
        });

        // making sure multiple is indeed multiple (only store multiple for merged relations)
        $relationships = collect($relationships)->map(function($link){
            if (count($link['properties']['types']) === 1) {
                $link['type'] = $link['properties']['types'][0];
                unset($link['multiple']);
            }

            return $link;
        });

        // user friendly relationship naming
        // also adds the `icon relation icon` html markup
        $relationships = collect($relationships)->map(function($link) use ($nodes){

            // the formatting requires the knowledge of the from node class and to node class
            $from = collect($nodes)->filter(function($node) use ($link) {
                return $node['id'] === $link['startNode'];
            })->first();

            $to = collect($nodes)->filter(function($node) use ($link) {
                return $node['id'] === $link['endNode'];
            })->first();

            $fromClass = $from['properties']['class'];
            $fromType = array_key_exists('type', $from['properties']) ? $from['properties']['type'] : null;
            $toClass = $to['properties']['class'];
            $toType = array_key_exists('type', $to['properties']) ? $to['properties']['type'] : null;

            $fromIcon = StrUtil::portalIconHTML($fromClass, $fromType);
            $toIcon = StrUtil::portalIconHTML($toClass, $toType);

            $relation = format_relationship($fromClass, $link['type'], 'EXPLICIT', $toClass);

            $link['type'] = $relation;
            $link['html'] = "$fromIcon $relation $toIcon";

            if (array_key_exists('multiple', $link)) {
                $link['html'] = '';
                foreach ($link['properties']['types'] as $type){
                    $relation = format_relationship($fromClass, $type, false, $toClass);
                    $link['html'] .= "$fromIcon $relation $toIcon<br/>";
                }
            }

            return $link;
        });

        // unsets not needed variables to make the map cleaner
        $relationships = collect($relationships)
            ->map(function($link) {
                unset($link['count']);
                unset($link['reverse']);
                unset($link['multiple']);
                return $link;
            })
            ->values()->toArray();

        return $this->formatForJSLibrary($nodes, $relationships);
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