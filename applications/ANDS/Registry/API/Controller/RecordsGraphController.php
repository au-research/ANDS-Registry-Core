<?php


namespace ANDS\Registry\API\Controller;


use ANDS\Cache\Cache;
use ANDS\Registry\API\Request;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\StrUtil;

class RecordsGraphController
{
    /**
     * api/registry/records/:id/graph
     * @param $id
     * @return mixed|null
     */
    public function index($id)
    {
        $disableCache = !! Request::get('cache');
        if (!$disableCache) {
            // caches by default
            // R28: does not accept custom parameters yet
            return Cache::file()->rememberForever("graph.$id", function() use ($id){
                return $this->getGraphForRecord($id);
            });
        }

        return $this->getGraphForRecord($id);
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