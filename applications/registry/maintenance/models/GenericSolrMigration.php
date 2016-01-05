<?php

namespace ANDS;


class GenericSolrMigration implements \GenericMigration
{

    public $ci;
    private $fields;
    private $copyFields;

    function __construct()
    {
        $this->ci =& get_instance();
        $this->fields = array();
        $this->copyFields = array();
    }

    function up()
    {
        return $this->ci->solr->schema(['add-field' => $this->fields]);
    }

    function down()
    {
        $delete_fields = [];
        foreach ($this->fields as $field) {
            $delete_fields[] = ['name' => $field['name']];
        }
        return $this->ci->solr->schema(['delete-field' => $delete_fields]);
    }

    /**
     * @param array $fields
     */
    function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getCopyFields()
    {
        return $this->copyFields;
    }

    /**
     * @param array $copyFields
     */
    public function setCopyFields($copyFields)
    {
        $this->copyFields = $copyFields;
    }
}