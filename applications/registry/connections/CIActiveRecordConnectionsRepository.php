<?php

namespace ANDS\Registry\Connections;

class CIActiveRecordConnectionsRepository {

    public function run($filters, $flags, $limit = 10, $offset = 0)
    {
        $this->db->select('*');
        foreach ($filters as $key=>$value) {
            if (!is_array($value)) {
                $this->db->where($key, $value);
            } else if (is_array($value)) {
                $this->db->where_in($key, $value);
            }
        }
        $this->db->limit($limit, $offset);
        $result = $this->db->from('relationships')->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return [];
    }

    public function __construct($db) {
        $this->db = $db;
    }

}