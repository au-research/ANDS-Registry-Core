<?php

namespace ANDS\Registry\Backup;

use Carbon\Carbon;

class Backup
{
    private $id;
    private $title;
    private $description;
    private $createdAt;
    private $modifiedAt;
    private $authors = [];
    private $dataSources = [];

    public static function create($id, $title = "", $description = "", $authors = [], $dataSources = []) {
        $backup = new static;
        $backup->id = $id;
        $backup->title = $title;
        $backup->description = $description;
        $backup->authors = $authors;
        $backup->dataSources = $dataSources;
        $backup->createdAt = Carbon::now()->toIso8601String();
        $backup->modifiedAt = Carbon::now()->toIso8601String();
        return $backup;
    }

    public static function parse($data) {
        $backup = new static;
        $backup->id = $data['id'];
        $backup->title = $data['title'];
        $backup->description = $data['description'];
        $backup->createdAt = $data['created_at'];
        $backup->modifiedAt = $data['modified_at'];
        $backup->authors = $data['authors'];
        return $backup;
    }

    public function toMetaArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->createdAt,
            'modified_at' => $this->modifiedAt,
            'authors' => $this->authors
        ];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * @return array
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * @return array
     */
    public function getDataSources()
    {
        return $this->dataSources;
    }



}