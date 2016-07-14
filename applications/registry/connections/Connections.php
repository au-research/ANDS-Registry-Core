<?php

namespace ANDS\Registry;

use ANDS\Registry\Connections\Relation as Relation;

class Connections {

    private $filters = [];
    private $flags = [
        'from_id',
        'from_key',
        'from_title',
        'from_slug',
        'from_class',
        'from_type',
        'relation_type',
        'relation_description',
        'relation_url',
        'relation_origin',
        'to_id',
        'to_key',
        'to_title',
        'to_slug',
        'to_class',
        'to_type'
    ];
    private $limit = 10;
    private $offset = 0;
    protected $repo = null;

    //r.registry_object_id, r.key, r.class, r.title, r.slug, r.status, rr.relation_type, rr.relation_description,  rr.relation_url, rr.origin, r.type

    public function setFilter($type, $value) {
        $this->filters[$type] = $value;
        return $this;
    }

    public function setFlag($flags)
    {
        $this->flags = $flags;
        return $this;
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

    public function convertRepoResultToRelationResult($repoResult)
    {
        $result = [];
        foreach ($repoResult as $row) {
            $relation = new Relation();
            foreach ($this->flags as $flag) {
                if (array_key_exists($flag, $row)) {
                    $relation->setProperty($flag, $row[$flag]);
                }
            }

            // don't add existing relation
            if (in_array($relation, $result)) {
                continue;
            }

            // merge relation

            $result[] = $relation;

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
        $result = $this->convertRepoResultToRelationResult($repoResult);
        return $result;
    }

    public function __construct($repository) {
        $this->repo = $repository;
        require_once(REGISTRY_APP_PATH.'connections/Relation.php');
    }
}