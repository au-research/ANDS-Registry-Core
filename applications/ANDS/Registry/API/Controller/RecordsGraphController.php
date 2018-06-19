<?php


namespace ANDS\Registry\API\Controller;


use ANDS\Cache\Cache;
use ANDS\Registry\Providers\GraphRelationshipProvider;
use ANDS\Registry\Providers\RIFCS\IdentifierProvider;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\StrUtil;

class RecordsGraphController
{

    protected $caching = false;

    /**
     * api/registry/records/:id/graph
     * @param $id
     * @return mixed|null
     */
    public function index($id)
    {
        if ($this->caching) {
            return Cache::remember("graph.$id", 30, function() use ($id){
                return $this->getGraphForRecord($id);
            });
        }

        return $this->getGraphForRecord($id);

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
                $classPlural = StrUtil::plural($clusterClass);
                $cluster['properties'] = array_merge($cluster['properties'], [
                    'title' => "$count related $classPlural",
                    'url' => constructPortalSearchQuery($filters),
                    'count' => $count,
                    'class' => 'cluster',
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

                $props = $node['properties'];

                if (array_key_exists('slug', $props)) {
                    $node['properties']['url'] = portal_url($node['properties']['slug'].'/'.$node['properties']['roId']);
                    return $node;
                }

                // TODO: tighten checks
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
                $link['count'] = collect($relationships)->filter(function($link2) use ($link){
                    return $link2['startNode'] === $link['startNode'] && $link2['endNode'] === $link['endNode'];
                })->count();
                return $link;
            })->map(function($link) use ($relationships){
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

                $link['multiple'] = false;
                $link['properties']['types'] = [ $link['type'] ];
                return $link;
            });

        // reverse relationship after being merged into a (priority) list will be removed
        $toRemove = [];

        // reverse
        $relationships = collect($relationships)
        ->sortByDesc('count')
        ->map(function($link) use ($relationships){
            $link['reverse'] = collect($relationships)->filter(function($link2) use ($link){
                return $link2['startNode'] === $link['endNode'] && $link2['endNode'] === $link['startNode'];
            });
            return $link;
        })->map(function($link) use ($relationships, &$toRemove){
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
        })->filter(function($link) use ($toRemove){
            return !in_array($link['id'], $toRemove);
        });

        // unique by start and end node
        $relationships = collect($relationships)->unique(function($link){
            return $link['startNode'].$link['endNode'];
        });

        // making sure multiple is indeed multiple
        $relationships = collect($relationships)->map(function($link){
            if (count($link['properties']['types']) === 1) {
                $link['type'] = $link['properties']['types'][0];
                unset($link['multiple']);
            }

            return $link;
        });

        // user friendly relationship naming
        $relationships = collect($relationships)->map(function($link) use ($nodes){
            $from = collect($nodes)->filter(function($node) use ($link) {
                return $node['id'] === $link['startNode'];
            })->first();

            $to = collect($nodes)->filter(function($node) use ($link) {
                return $node['id'] === $link['endNode'];
            })->first();

            $fromClass = array_key_exists('class', $from['properties']) ? $from['properties']['class'] : 'cluster';
            $toClass = array_key_exists('class', $to['properties']) ? $to['properties']['class'] : 'cluster';

            $fromIcon = $this->getIconFor($from);
            $toIcon = $this->getIconFor($to);

            $relation = format_relationship($fromClass, $link['type'], false, $toClass);

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

        $relationships = collect($relationships)
            ->map(function($link) {
                unset($link['count']);
                unset($link['reverse']);
                unset($link['multiple']);
                return $link;
            })
            ->values()->toArray();

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

    private function getIconFor($node)
    {
        $icon = '';
        $labels = $node['labels'];

        if (in_array('collection', $labels)) {
            $icon = 'fa-folder-open';
        } elseif (in_array('activity', $labels)) {
            $icon = 'fa-flask';
        } elseif (in_array('service', $labels)) {
            $icon = 'fa-wrench';
        } elseif (in_array('group', $labels)) {
            $icon = 'fa-group';
        } elseif (in_array('party', $labels)) {
            $icon = 'fa-user';
        } elseif (in_array('website', $labels)) {
            $icon = 'fa-globe';
        } elseif (in_array('publication', $labels)) {
            $icon = 'fa-book';
        }

        return "<i class='fa $icon icon-portal'></i>";
    }
}