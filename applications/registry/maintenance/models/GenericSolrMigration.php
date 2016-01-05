<?php

namespace ANDS;


class GenericSolrMigration implements \GenericMigration
{

    public $ci;
    private $fields;

    function __construct()
    {
        $this->ci =& get_instance();
    }

    function setFields($fields) {
        $this->fields = $fields;
    }

    function getFields() {
        return $this->fields;
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
}