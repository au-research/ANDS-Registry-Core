<?php

namespace ANDS;


class GenericSolrMigration implements \GenericMigration
{

    public $ci;
    private $fields;
    private $copyFields;
    private $dynamicFields;
    private $core;

    function __construct()
    {
        $this->ci =& get_instance();
        $this->fields = array();
        $this->copyFields = array();
        $this->core = 'portal';
    }

    function up()
    {
        $this->ci->solr->setCore($this->getCore());

        //set up existing data
        $existingSchema = $this->ci->solr->getSchema();
        $existingFields = $existingSchema['schema']['fields'];
        $existingDynamicFields = $existingSchema['schema']['dynamicFields'];
        $existingCopyFields = $existingSchema['schema']['copyFields'];

        $existingFieldsName = array();
        foreach($existingFields as $field) {
            $existingFieldsName[] = $field['name'];
        }
        $existingDynamicFieldsName = array();
        foreach ($existingDynamicFields as $field) {
            $existingDynamicFieldsName[] = $field['name'];
        }
        $existingCopyFieldsName = array();
        foreach ($existingCopyFields as $field) {
            $existingCopyFieldsName[$field['source']] = $field['dest'];
        }

        // Fields
        $replaceFields = array();
        $addFields = array();
        foreach ($this->getFields() as $field) {
            if (in_array($field['name'], $existingFieldsName)) {
                $replaceFields[] = $field;
            } else {
                $addFields[] = $field;
            }
        }

        $data = array();

        if (sizeof($replaceFields) > 0) {
            $data['replace-field'] = $replaceFields;
        }

        if (sizeof($addFields) > 0) {
            $data['add-field'] = $addFields;
        }

        //Dynamic Fields
        $replaceDynamicFields = array();
        $addDynamicFields = array();
        if ($this->getDynamicFields()) {
            foreach ($this->getDynamicFields() as $field) {
                if (in_array($field['name'], $existingDynamicFieldsName)) {
                    $replaceDynamicFields[] = $field;
                } else {
                    $addDynamicFields[] = $field;
                }
            }
        }

        if (sizeof($replaceDynamicFields)> 0) {
            $data['replace-dynamic-field'] = $replaceDynamicFields;
        }

        if (sizeof($addDynamicFields) > 0) {
            $data['add-dynamic-field'] = $addDynamicFields;
        }

        //Copy Fields
        $replaceCopyFields = array();
        $addCopyFields = array();
        foreach ($this->getCopyFields() as $field) {
            $key = $field['source'];
            if (isset($existingCopyFieldsName[$key])) {
                if ($field['dest'] == $existingCopyFieldsName[$key]) {
                    $replaceCopyField[] = $field;
                }
            } else {
                $addCopyFields[] = $field;
            }
        }

        if (sizeof($replaceCopyFields)> 0) {
            $data['replace-copy-field'] = $replaceCopyFields;
        }

        if (sizeof($addCopyFields) > 0) {
            $data['add-copy-field'] = $addCopyFields;
        }

        if (sizeof($data) > 0) {
            return $this->ci->solr->schema($data);
        }
    }

    function down()
    {
        $result = [];
        $this->ci->solr->setCore($this->getCore());
        $delete_fields = [];
        foreach ($this->fields as $field) {
            $delete_fields[] = ['name' => $field['name']];
        }

        //delete copyFields first
        // todo error handling
        if (sizeof($this->getCopyFields()) > 0) {
            $result[] = $this->ci->solr->schema(['delete-copy-field'=>$this->getCopyFields()]);
        }

        //then delete the fields
        $result[] =  $this->ci->solr->schema(['delete-field' => $delete_fields]);
        return $result;
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

    /**
     * @return string
     */
    public function getCore()
    {
        return $this->core;
    }

    /**
     * @param string $core
     */
    public function setCore($core)
    {
        $this->core = $core;
        $this->ci->solr->setCore($this->getCore());
    }

    /**
     * @return mixed
     */
    public function getDynamicFields()
    {
        return $this->dynamicFields;
    }

    /**
     * @param mixed $dynamicFields
     */
    public function setDynamicFields($dynamicFields)
    {
        $this->dynamicFields = $dynamicFields;
    }
}