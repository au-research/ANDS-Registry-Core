<?php


namespace ANDS\Registry\API\Controller;


use ANDS\Cache\Cache;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Repository\RegistryObjectsRepository;

class RecordsGraphController
{
    /**
     * api/registry/records/:id/graph
     * @param $id
     * @return mixed|null
     */
    public function index($id)
    {
        return Cache::remember("graph.$id", 30, function() use ($id){
            return $this->getGraphForRecord($id);
        });
    }

    /**
     * TODO: Refactor to GraphRelationships getforportal?
     * TODO: accepts parameters for different options
     *
     * @param $id
     * @return array
     */
    private function getGraphForRecord($id)
    {
        $record = RegistryObjectsRepository::getRecordByID($id);
        $graph = GraphRelationshipProvider::getByID($id);

        $nodes = array_values($graph['nodes']);
        $relationships = array_values($graph['links']);
        $clusters = collect($nodes)
            ->filter(function ($item) {
                return in_array("cluster", $item['labels']);
            })->map(function ($cluster) use ($relationships, $record) {

                if (collect($cluster['labels'])->contains('RelatedInfo')) {
                    return $cluster;
                }

                $clusterClass = collect($cluster['labels'])->filter(function ($label) {
                    return in_array($label, ['collection', 'service', 'activity', 'party']);
                })->first();

                // related_party_multi_id construction
                $searchClass = $record->class;
                if ($record->class == 'party') {
                    $searchClass = (strtolower($record->type) == 'group') ? 'party_multi' : 'party_one';
                }

                // find the relationship that connects this cluster to the current node
                $relation = collect($relationships)->filter(function ($rel) use ($cluster) {
                    return $rel['endNode'] === $cluster['id'];
                })->first();

                $filters = [
                    'class' => $clusterClass,
                    "related_{$searchClass}_id" => $record->id,
                    'relation' => $relation['type']
                ];

                $count = getSolrCountForQuery($filters);
                $cluster['properties'] = array_merge($cluster['properties'], [
                    'title' => "$count related $clusterClass",
                    'url' => constructPortalSearchQuery($filters),
                    'count' => $count,
                    'clusterClass' => $clusterClass
                ]);

                return $cluster;
            });

        $nodes = collect($nodes)
            ->map(function ($node) use ($clusters) {
                if (!in_array($node['id'], $clusters->pluck('id')->toArray(), true)) {
                    return $node;
                }
                return $clusters->filter(function ($c) use ($node) {
                    return $c['id'] == $node['id'];
                })->first();
            })->map(function($node) {
                if (array_key_exists('slug', $node['properties'])) {
                    $node['properties']['url'] = portal_url($node['properties']['slug'].'/'.$node['properties']['roId']);
                    return $node;
                }
                return $node;
            })->values()->toArray();

        // user friendly relationship naming
        $relationships = collect($relationships)->map(function($link) use ($nodes){
            $from = collect($nodes)->filter(function($node) use ($link) {
                return $node['id'] === $link['startNode'];
            })->first();

            $to = collect($nodes)->filter(function($node) use ($link) {
                return $node['id'] === $link['endNode'];
            })->first();

            $fromClass = array_key_exists('class', $from['properties']) ? $from['properties'] : 'cluster';

            $toClass = array_key_exists('class', $to['properties']) ? $to['properties'] : 'cluster';

            $link['type'] = format_relationship($fromClass, $link['type'], false, $toClass);

            return $link;
        });

        // format for neo4jd3 js library
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