<?php


namespace ANDS\Repository;

use ANDS\Registry\ImplicitRelationshipView;
use ANDS\Registry\RelationshipView;
use ANDS\RegistryObject\ImplicitRelationship;

/**
 * Class EloquentConnectionsRepository
 * @package ANDS\Repository
 */
class EloquentConnectionsRepository
{
    private $viewSource = RelationshipView::class;
    /**
     * EloquentConnectionsRepository constructor.
     */
    public function __construct()
    {
        initEloquent();
    }

    /**
     * Primary function
     *
     * @param $filters
     * @param $flags
     * @param int $limit
     * @param int $offset
     * @return mixed
     * @internal param $flag
     */
    public function run($filters, $flags = [], $limit = 2000, $offset = 0)
    {
        // [key => value]
        $singleFilters = collect($filters)->filter(function($item, $key){
            return !is_array($item) && strpos($key, "!=") === false;
        })->toArray();

        // [key => [value1, value2]
        $arrayFilters = collect($filters)->filter(function($item){
            return is_array($item);
        })->toArray();

        // [key]
        $rawFilters = collect($filters)->filter(function($item, $key){
            return strpos($key, "!=") > 0;
        })->keys()->toArray();

        // deal with single valued filters
        $relationship = RelationshipView::where($singleFilters);
        if ($this->getViewSource() == ImplicitRelationshipView::class) {
            $relationship = ImplicitRelationshipView::where($singleFilters);
        }

        // where_in filters
        foreach ($arrayFilters as $key=>$value) {
            $relationship = $relationship->whereIn($key, $value);
        }

        // $rawFilters
        foreach ($rawFilters as $filter) {
            $relationship = $relationship->whereRaw($filter);
        }

        if ($limit > 0) {
            $relationship = $relationship->limit($limit)->offset($offset);
        }

        $relationship = $relationship
            ->get();

        return $relationship->toArray();
    }

    /**
     * @param mixed $viewSource
     * @return EloquentConnectionsRepository
     */
    public function setViewSource($viewSource)
    {
        $this->viewSource = $viewSource;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getViewSource()
    {
        return $this->viewSource;
    }

}