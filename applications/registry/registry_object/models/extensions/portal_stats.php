<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Portal_stats_Extension extends ExtensionBase
{
    function __construct($ro_pointer)
    {
        parent::__construct($ro_pointer);
    }

    function getAllPortalStat() {
        $portal_db = $this->_CI->load->database('portal', true);
        $stat = array(
            'viewed' => 0,
            'cited' => 0,
            'accessed' => 0
        );
        $stat_query = $portal_db->get_where('record_stats', array('ro_id'=>$this->ro->id));
        if ($stat_query->num_rows() > 0) {
            $result = $stat_query->first_row('array');
            $stat['viewed'] = $result['viewed'];
            $stat['cited'] = $result['cited'];
            $stat['accessed'] = $result['accessed'];
        }
        return $stat;
    }

    function getPortalStat($statistic) {
        $portal_db = $this->_CI->load->database('portal', true);
        $stat = $portal_db->get_where('record_stats', array('ro_id'=>$this->ro->id));
        if ($stat->num_rows() > 0) {
            $result = $stat->first_row('array');
            if (isset($result[$statistic])) {
                return $result[$statistic];
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
}