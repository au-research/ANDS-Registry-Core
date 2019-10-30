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
    // use this list when searching for the topParent
    private $processedParentList = [];

    // use this list when building the tree from top down
    private $processedChildrenList = [];
    private $topParent = null;
    private $limit = 100;

    public function getNestedCollections($parentKey, $width = 5)
    {
        if($width === 0)
            return [];
        $this->processedChildrenList[] = $parentKey;
        $currentChildrenList = [];
        $links = $this
            ->init()
            ->setFilter('from_key', $parentKey)
            ->setLimit($this->limit)
            ->setFilter('to_class', 'collection')
            ->setFilter('to_status', 'PUBLISHED')
            ->setFilter('relation_type', 'hasPart')
            ->get();


        foreach ($links as $key => &$relation) {
            $to_key = $relation->getProperty('to_key');
            $relation = $relation->flip();
            if($to_key != $parentKey && !in_array($to_key, $currentChildrenList)) {
                $currentChildrenList[] = $to_key;
                if(!in_array($to_key, $this->processedChildrenList)){
                    $this->processedChildrenList[] = $to_key;
                    $nested = $this->getNestedCollections($to_key, $width - 1);
                    if (sizeof($nested) > 0) {
                        $links[$key]->setProperty('children', $nested);
                    }
                 }
            }else{
                unset($links[$key]);
            }

        }

        $reverseLinks = $this
            ->init()
            ->setFilter('to_key', $parentKey)
            ->setLimit($this->limit)
            ->setFilter('from_class', 'collection')
            ->setFilter('from_status', 'PUBLISHED')
            ->setFilter('relation_type', 'isPartOf')
            ->get();


        foreach ($reverseLinks as $key => &$relation) {
            $to_key = $relation->getProperty('from_key');
           // remove duplicate children
            if($to_key != $parentKey && !in_array($to_key, $currentChildrenList)){
                $currentChildrenList[] = $to_key;
                // to avoid recursion don't follow brunches that are already done ones
                if(!in_array($to_key, $this->processedChildrenList)) {
                    $this->processedChildrenList[] = $to_key;
                    $nested = $this->getNestedCollections($to_key, $width - 1);
                    if (sizeof($nested) > 0) {
                        $reverseLinks[$key]->setProperty('children', $nested);
                    }
                }
            }else{
                unset($reverseLinks[$key]);
            }
        }

        return array_slice(array_merge($links, $reverseLinks), 0 , $this->limit);
    }

    /**
     * From any node, find the top parent node, then getNestedCollections
     *
     * @param $key
     * @param int $width
     * @return Relation[]
     */
    public function getNestedCollectionsFromChild($key, $width = 5)
    {

        $this->getTopParents($key);
        $topParent = RegistryObjectsRepository::getPublishedByKey($this->topParent);
        $nestedCollections = [];
        $nestedCollection = new Relation([
            'from_id' => $topParent->id,
            'from_title' => $topParent->title,
            'from_class' => $topParent->class,
            'from_slug' => $topParent->slug,
            'from_status' => $topParent->status,
            'relation_type' => 'hasPart',
            'children' => $this->getNestedCollections($this->topParent,  $width - 1)
        ]);
// chek if we left out some parents (https://test.ands.org.au/card-indexes-family-community-services/619832)
        $nestedCollections[] = $nestedCollection;
        //dd($this->processedParentList);
        foreach ($this->processedParentList as $toParent){

            if(!in_array($toParent, $this->processedChildrenList)){
                $topParent = RegistryObjectsRepository::getPublishedByKey($toParent);
                $nestedCollection = new Relation([
                    'from_id' => $topParent->id,
                    'from_title' => $topParent->title,
                    'from_class' => $topParent->class,
                    'from_slug' => $topParent->slug,
                    'from_status' => $topParent->status,
                    'relation_type' => 'hasPart',
                    'children' => $this->getNestedCollections($toParent,  $width - 1)
                ]);
                $nestedCollections[] = $nestedCollection;
            }
        }

        return $nestedCollections;
    }

    /**
     * Return an array of all parents in the nested connections
     *
     * @param $key
     * @return array
     */
    public function getTopParents($key)
    {
        if(in_array($key, $this->processedParentList)){
            $this->topParent = $key;
            return $key;
        }
        $this->processedParentList[] = $key;
        $moreParents = [];
        $parents = $this
            ->init()
            ->setFilter('to_key', $key)
            ->setFilter('to_status', 'PUBLISHED')
            ->setLimit(5)
            ->setFilter('from_class', 'collection')
            ->setFilter('relation_type', 'hasPart')
            ->get();
        if(sizeof($parents) > 0){
            foreach ($parents as $relation) {
                $from_key = $relation->getProperty('from_key');
                if(!in_array($from_key, $this->processedParentList)) {
                    $moreParents[] = $this->getTopParents($from_key);
                }
            }
        }

        $parents = [];

        $parents = $this
            ->init()
            ->setReverse(true)
            ->setFilter('from_key', $key)
            ->setLimit(5)
            ->setFilter('to_class', 'collection')
            ->setFilter('to_status', 'PUBLISHED')
            ->setFilter('relation_type', 'isPartOf')
            ->get();
        if(sizeof($parents) > 0) {
            foreach ($parents as $relation) {
                $to_key = $relation->getProperty('to_key');
                if(!in_array($to_key, $this->processedParentList)) {
                    $moreParents[] = $this->getTopParents($to_key);
                }
            }
        }
        if (sizeof($moreParents) == 0) {
            $this->topParent = $key;
        }
    }


}