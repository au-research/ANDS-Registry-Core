<?php

namespace ANDS\Registry;

use ANDS\Registry\Relation as Relation;
use ANDS\Repository\EloquentConnectionsRepository;

class PreMyceliumConnections {

    private $filters = [];
    private $flags = [];
    private $limit = 10;
    private $offset = 0;
    private $extractReverse = false;
    protected $repo = null;

    //r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type, rr.relation_description,  rr.relation_url, rr.origin, r.type

    public function setFilter($type, $value = null) {
        $this->filters[$type] = $value;
        return $this;
    }

    public function setFlag($flags)
    {
        $this->flags = $flags;
        return $this;
    }

    public function setReverse($value)
    {
        $this->extractReverse = $value;
        return $this;
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function get()
    {
        $repoResult = $this->repo->run($this->filters, $this->flags, $this->limit, $this->offset);
        $result = $this->convertRepoResultToRelationResult($repoResult);
        return $result;
    }

    public function count()
    {
        return $this->repo->countResult($this->filters, $this->flags, $this->limit, $this->offset);
    }

    public function convertRepoResultToRelationResult($repoResult)
    {
        $result = [];
        if(count($repoResult) == 0)
        {
            return $result;
        }
        foreach ($repoResult as $row) {

            $relation = new Relation();
            foreach ($row as $key => $value) {
                $relation->setProperty($key, $value);
            }

            // determine key
            // @todo extend for reverse link to_key
            if ($this->extractReverse) {
                $key = md5($row['to_key'].$row['from_key']);
                $relation = $relation->flip();
            } else {
                $key = md5($row['from_key'].$row['to_key']);
            }

            // @todo deal with missing to_key (identifier relation)
            if ($row['to_key'] === null && $row['to_identifier'] != null) {
                $key = md5($row['from_key'].$row['to_identifier']);
            }

            // don't add existing relation
            if (in_array($relation, $result)) {
                continue;
            }

            // merge relation
            $parsedRelation = $relation;
            if (array_key_exists($key, $result)) {
                $parsedRelation->mergeWith($result[$key]->getProperties());
            }

            $result[$key] = $parsedRelation;
        }
        return $result;
    }

    public function getExplicitRelationByKey($key, $limit = 10, $offset = 0)
    {
        $repoResult = $this->repo->run(
            [ 'from_key' => $key ]
            , $this->flags, $limit, $offset
        );
        $result = $this->convertRepoResultToRelationResult($repoResult);
        return $result;
    }

    public function getReverseRelationByKey($key, $limit = 10, $offset = 0)
    {
        $repoResult = $this->repo->run(
            [ 'to_key' => $key ]
            , $this->flags, $limit, $offset
        );
        $this->extractReverse = true;
        $result = $this->convertRepoResultToRelationResult($repoResult);
        return $result;
    }

    /**
     * @return static
     */
    public static function create()
    {
        return static::getStandardProvider();
    }

    /**
     * @return static
     */
    public static function getStandardProvider()
    {
        return new static(new EloquentConnectionsRepository());
    }

    /**
     * @return static
     */
    public static function getIdentifierProvider()
    {
        $repository = new EloquentConnectionsRepository();
        $repository->setViewSource(IdentifierRelationshipView::class);
        $provider = new static($repository);
        $provider->setFlag([
            '*'
        ]);
        return $provider;
    }

    /**
     * @return static
     */
    public static function getImplicitProvider()
    {
        $repository = new EloquentConnectionsRepository();
        $repository->setViewSource(ImplicitRelationshipView::class);
        return new static($repository);
    }

    public function __construct($repository) {
        $this->repo = $repository;
        $this->init();
    }

    public function init()
    {
        $this->filters = [];
        $this->flags = [
            'from_id',
            'from_key',
            'from_title',
            'from_slug',
            'from_class',
            'from_type',
            'from_data_source_id',
            'relation_type',
            'relation_description',
            'relation_url',
            'relation_origin',
            'to_id',
            'to_key',
            'to_title',
            'to_slug',
            'to_class',
            'to_type',
            'to_data_source_id'
        ];
        $this->limit = 10;
        $this->offset = 0;
        $this->extractReverse = false;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }
}