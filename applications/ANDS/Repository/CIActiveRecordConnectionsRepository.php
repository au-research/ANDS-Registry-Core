<?php

namespace ANDS\Repository;

class CIActiveRecordConnectionsRepository
{

    public function run($filters, $flags = [], $limit = 20000, $offset = 0)
    {
        $this->db->select('*');
        foreach ($filters as $key => $value) {
            if ($value === null) {
                $this->db->where($key);
            } else {
                if (!is_array($value)) {
                    $this->db->where($key, $value);
                } elseif (is_array($value)) {
                    if (is_array($value)) {
                        $this->db->where_in($key, $value);
                    }
                }
            }
        }

        $this->db->limit($limit, $offset);
        $result = $this->db->from('relationships')->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }

        return [];
    }

    public function __construct($db = false)
    {
        if ($db === false) {
            $ci =& get_instance();
            $db = $ci->db;
        }
        $this->db = $db;
    }

}