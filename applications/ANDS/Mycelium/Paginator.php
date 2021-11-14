<?php

namespace ANDS\Mycelium;

use MinhD\SolrClient\SolrSearchResult;

class Paginator
{
    private $data = [];

    /**
     * Generates a new Paginator instance from a SolrResult
     *
     * @param \MinhD\SolrClient\SolrSearchResult $solrResult
     * @return \ANDS\Mycelium\Paginator
     */
    public static function fromSolrResult(SolrSearchResult $solrResult)
    {
        if ($solrResult->getParams() === null) {
            return new Paginator();
        }

        if ($solrResult->errored()) {
            throw new \Exception($solrResult->getErrorMessage());
        }

        $paginator = new Paginator();

        $paginator->perPage = intval($solrResult->getParam('pp'));
        $paginator->offset = intval($solrResult->getParam('offset'));
        $paginator->sort = $solrResult->getParam('sort');
        $paginator->count = intval($solrResult->getParam('rows'));
        $paginator->total = intval($solrResult->getNumFound());

        // massage the data a bit
        $contents = $solrResult->getDocs('json');
        $contents = json_decode($contents, true);
        $contents = collect($contents)->map(function ($item) {
            // convert _childDocuments_ into relations for anonymous child documents
            if (array_key_exists('_childDocuments_', $item)) {
                $item['relations'] = $item['_childDocuments_'];
                unset($item['_childDocuments_']);
            }

            if (!array_key_exists('relations', $item)) {
                // todo probably issue a warning here
                return $item;
            }

            // set relation_internal and relation_reverse to proper boolean value
            $item['relations'] = collect($item['relations'])->map(function ($relation) {
                if (isset($relation['relation_internal'])) {
                    $relation['relation_internal'] = boolval($relation['relation_internal']);
                }

                if (isset($relation['relation_reverse'])) {
                    $relation['relation_reverse'] = boolval($relation['relation_reverse']);
                }

                unset($relation['_root_']);
                unset($relation['_version_']);

                // sort the keys alphabetically
                ksort($relation);

                return $relation;
            })->toArray();

            unset($item['_root_']);
            unset($item['_version_']);

            // sort the keys alphabetically
            ksort($item);

            return $item;
        });

        $paginator->contents = $contents->toArray();
        return $paginator;
    }

    /**
     * The array representation of this Paginator instance
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'total' => $this->total,
            'count' => $this->count,
            'per_page' => $this->perPage,
            'offset' => $this->offset,
            'sort' => $this->sort,
            'contents' => $this->contents
        ];
    }

    /**
     * the JSON string representation of this Paginator instance
     *
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

}