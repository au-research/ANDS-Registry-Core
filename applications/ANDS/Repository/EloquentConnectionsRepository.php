<?php


namespace ANDS\Repository;

use ANDS\Registry\IdentifierRelationshipView;
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
    private $query;

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
        $this->query = $this->constructQuery($filters, $flags, $limit, $offset);
        // debug("SQL: ". $this->query->toSql());
        return $this->query->get()->toArray();
    }

    /**
     * @param $filters
     * @param array $flags
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function countResult($filters, $flags = [], $limit = 2000, $offset = 0)
    {
        $this->query = $this->constructQuery($filters, $flags, $limit, $offset);
        return $this->query->count();
    }

    /**
     * @param $filters
     * @param array $flags
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    private function constructQuery($filters, $flags = [], $limit = 2000, $offset = 0)
    {

        // [key => value]
        $singleFilters = collect($filters)->filter(function($item, $key){
            return !is_array($item) && strpos($key, "!=") === false && $item != null;
        })->toArray();

        // [key => [value1, value2]
        $arrayFilters = collect($filters)->filter(function($item){
            return is_array($item);
        })->toArray();

        // [key]
        $rawFilters = collect($filters)->filter(function($item, $key){
            return strpos($key, "!=") > 0 || $item === null;
        })->keys()->toArray();

        // deal with single valued filters
        $relationship = RelationshipView::where($singleFilters);

        if ($this->getViewSource() == ImplicitRelationshipView::class) {
            $relationship = ImplicitRelationshipView::where($singleFilters);
        }

        if ($this->getViewSource() == IdentifierRelationshipView::class) {
            $relationship = IdentifierRelationshipView::where($singleFilters);
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

        $this->query = $relationship;
        return $this->query;
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