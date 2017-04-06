<?php

namespace ANDS\Registry\Providers;

use ANDS\Registry\Connections;
use ANDS\Registry\Relation;
use ANDS\Repository\RegistryObjectsRepository;

/**
 * Class NestedConnectionsProvider
 * @package ANDS\Registry\Connections
 */
class NestedConnectionsProvider extends Connections
{
    /**
     * return a list of nested collections with children
     *
     * @param $key
     * @param int $width
     * @return array
     */
    public function getNestedCollections($key, $width = 5)
    {
        $limit = 100;
        $links = $this
            ->init()
            ->setFilter('from_key', $key)
            ->setLimit($limit)
            ->setFilter('to_class', 'collection')
            ->setFilter('to_status', 'PUBLISHED')
            ->setFilter('relation_type', 'hasPart')
            ->get();

        $reverseLinks = $this
            ->init()
            ->setReverse(true)
            ->setFilter('to_key', $key)
            ->setLimit($limit)
            ->setFilter('from_class', 'collection')
            ->setFilter('from_status', 'PUBLISHED')
            ->setFilter('relation_type', 'isPartOf')
            ->get();

        $links = array_merge($links, $reverseLinks);

        if ($width <= 0 || count($links) == 0) {
            return $links;
        }

        foreach ($links as $key => $relation) {
            $nested = $this->getNestedCollections($relation->getProperty('to_key'), $width - 1);
            if (sizeof($nested) > 0) {
                $links[$key]->setProperty('children', $nested);
            }
        }

        return $links;
    }

    /**
     * From any node, find the top parent node, then getNestedCollections
     *
     * @param $key
     * @param int $width
     * @return array
     */
    public function getNestedCollectionsFromChild($key, $width = 5)
    {
        $parents = $this->getParentNestedCollections($key);

        $startFrom = $key;

        if (sizeof($parents) > 0) {
            $topParent = array_pop($parents);
            $topParent = array_shift(array_values($topParent));
            $parentKey = $topParent->getProperty('from_key');
            if ($topParent->isReverse()) {
                $parentKey = $topParent->getProperty('to_key');
            }
            $startFrom = $parentKey;
        }

        $width = $width - 1;

        $topParent = RegistryObjectsRepository::getPublishedByKey($startFrom);
        $nestedCollection = new Relation([
            'from_id' => $topParent->id,
            'from_title' => $topParent->title,
            'from_class' => $topParent->class,
            'from_slug' => $topParent->slug,
            'from_status' => $topParent->status,
            'relation_type' => 'hasPart',
            'children' => $this->init()->getNestedCollections($startFrom, $width)
        ]);

        return $nestedCollection;
    }

    /**
     * Return an array of all parents in the nested connections
     *
     * @param $key
     * @return array
     */
    public function getParentNestedCollections($key)
    {
        $parents = $this
            ->init()
            ->setFilter('to_key', $key)
            ->setFilter('to_status', 'PUBLISHED')
            ->setLimit(200)
            ->setFilter('from_class', 'collection')
            ->setFilter('relation_type', 'hasPart')
            ->get();

        $reverseLinks = $this
            ->init()
            ->setReverse(true)
            ->setFilter('from_key', $key)
            ->setLimit(200)
            ->setFilter('to_class', 'collection')
            ->setFilter('to_status', 'PUBLISHED')
            ->setFilter('relation_type', 'isPartOf')
            ->get();

        $parents = array_merge($parents, $reverseLinks);

        foreach ($parents as $key=>$relation) {
            $moreParents = $this->getParentNestedCollections($relation->getProperty('from_key'));
            if (sizeof($moreParents) > 0) {
                $parents[] = $moreParents;
            }
        }

        return $parents;
    }

}