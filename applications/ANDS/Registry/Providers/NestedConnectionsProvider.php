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
        //var_dump("parent: " . $parentKey. " width: ". $width);
        //var_dump($this->processedChildrenList);
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
            //var_dump("to_key1: " . $to_key);
            if(!in_array($to_key, $this->processedChildrenList)){
                $nested = $this->getNestedCollections($to_key, $width - 1);
                if (sizeof($nested) > 0) {
                    $links[$key]->setProperty('children', $nested);
                }
            }
        }

        $reverseLinks = $this
            ->init()
            ->setReverse(true)
            ->setFilter('to_key', $parentKey)
            ->setLimit($this->limit)
            ->setFilter('from_class', 'collection')
            ->setFilter('from_status', 'PUBLISHED')
            ->setFilter('relation_type', 'isPartOf')
            ->get();

        //var_dump($reverseLinks);

        foreach ($reverseLinks as $key => &$relation) {

            $relation = $relation->flip();
            $to_key = $relation->getProperty('from_key');
            //var_dump("to_key2: " . $to_key);
            if(!in_array($to_key, $this->processedChildrenList)){
                $nested = $this->getNestedCollections($to_key, $width - 1);
                //var_dump($nested);
                if (sizeof($nested) > 0) {
                    $reverseLinks[$key]->setProperty('children', $nested);
                }
            }
        }

        return array_merge($links, $reverseLinks);
    }

    /**
     * From any node, find the top parent node, then getNestedCollections
     *
     * @param $key
     * @param int $width
     * @return Relation
     */
    public function getNestedCollectionsFromChild($key, $width = 5)
    {

        $this->getTopParents($key);
        //dd("START_FROM: " . $this->topParent);
        $topParent = RegistryObjectsRepository::getPublishedByKey($this->topParent);

        $nestedCollection = new Relation([
            'from_id' => $topParent->id,
            'from_title' => $topParent->title,
            'from_class' => $topParent->class,
            'from_slug' => $topParent->slug,
            'from_status' => $topParent->status,
            'relation_type' => 'hasPart',
            'children' => $this->getNestedCollections($this->topParent,  $width - 1)
        ]);

        return $nestedCollection;
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
            //var_dump("Already been there" . $key);
            $this->topParent = $key;
            return $key;
        }
        $this->processedParentList[] = $key;
        $moreParents = [];
        //var_dump("getTopParents" . $key);
        $parents = $this
            ->init()
            ->setFilter('to_key', $key)
            ->setFilter('to_status', 'PUBLISHED')
            ->setLimit(200)
            ->setFilter('from_class', 'collection')
            ->setFilter('relation_type', 'hasPart')
            ->get();
        //var_dump("getTopParents" . $key);
        if(sizeof($parents) > 0){
            foreach ($parents as $relation) {
                $from_key = $relation->getProperty('from_key');
                //var_dump("getTopParents sub1: " . $from_key);
                $moreParents[] = $this->getTopParents($from_key);
            }
        }

        $parents = [];

        $parents = $this
            ->init()
            ->setReverse(true)
            ->setFilter('from_key', $key)
            ->setLimit(200)
            ->setFilter('to_class', 'collection')
            ->setFilter('to_status', 'PUBLISHED')
            ->setFilter('relation_type', 'isPartOf')
            ->get();
        //var_dump("getTopParents" . $key);
        if(sizeof($parents) > 0) {
            foreach ($parents as $relation) {
                $from_key = $relation->getProperty('from_key');
                //var_dump("getTopParents sub2: " . $from_key);
                $moreParents[] = $this->getTopParents($from_key);
            }
        }
        //var_dump("getTopParents4" . $key);
        if (sizeof($moreParents) == 0) {
            //var_dump("return: " . $key);
            $this->topParent = $key;
        }
    }


}