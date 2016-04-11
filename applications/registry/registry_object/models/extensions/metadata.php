<?php


/**
 * Class Metadata_Extension
 */
class Metadata_Extension extends ExtensionBase
{
    /**
     * Metadata_Extension constructor.
     *
     * @param $ro_pointer
     */
    function __construct($ro_pointer)
    {
        parent::__construct($ro_pointer);
    }

    /**
     * Returns the metadata by name from the database
     *
     * @param           $name
     * @param bool|true $graceful
     * @return null
     * @throws Exception
     */
    function getMetadata($name, $graceful = true)
    {
        $query = $this->db->get_where("registry_object_metadata",
            array('registry_object_id' => $this->id, 'attribute' => $name));
        if ($query->num_rows() == 1) {
            $result_array = $query->result_array();
            $query->free_result();
            return $result_array[0]['value'];
        } else {
            if (!$graceful) {
                throw new Exception("Unknown/NULL metadata attribute requested by get_metadata($name) method");
            } else {
                return null;
            }
        }
    }

    /**
     * Sets the metadata by name,value
     * Saved in the database
     *
     * @param        $name
     * @param string $value
     */
    public function setMetadata($name, $value = '')
    {
        $query = $this->db
            ->get_where("registry_object_metadata",
                array('registry_object_id' => $this->id, 'attribute' => $name));
        if ($query->num_rows() == 1) {
            $this->db->where(array('registry_object_id' => $this->id, 'attribute' => $name));
            $this->db->update('registry_object_metadata', array('value' => $value));
        } else {
            $this->db->insert('registry_object_metadata',
                array('registry_object_id' => $this->id, 'attribute' => $name, 'value' => $value));
        }
    }

    /**
     * Deletes a metadata by name from the database
     *
     * @param $name
     * @return bool
     */
    public function deleteMetadata($name)
    {
        $query = $this->db
            ->where('registry_object_id', $this->id)
            ->where('attribute',$name)
            ->delete('registry_object_metadata');
        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}