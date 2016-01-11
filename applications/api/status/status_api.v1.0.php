<?php
/**
 * Class:  Status API
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API;

use \Exception as Exception;


class Status_api
{
    public function __construct()
    {
        $this->ci = &get_instance();
        $this->db = $this->ci->load->database('registry', true);
    }

    public function handle($method = array())
    {
        $this->params = array(
            'submodule' => isset($method[1]) ? $method[1] : false,
            'identifier' => isset($method[2]) ? $method[2] : false,
            'object_module' => isset($method[3]) ? $method[3] : false,
        );

        switch (strtolower($this->params['submodule'])) {
            default:
                if ($this->params['submodule']) {
                    return $this->reportFor($this->params['submodule']);
                } else {
                    return $this->report();
                }
                break;
        }
    }

    private function reportFor($module)
    {
        if ($module == 'harvester' || $module == 'task') {
           return $this->getDaemonStatus($module);
        }
        return false;
    }

    private function report()
    {
        $result = [
            'harvester' => $this->getDaemonStatus('harvester'),
            'task' => $this->getDaemonStatus('task')
        ];
        return $result;
    }

    private function getDaemonStatus($daemon)
    {
        if ($daemon == 'harvester') {
            $query = $this->db->get_where('configs', ['key' => 'harvester_status']);
            $queryResult = $query->first_row();
            $status = json_decode($queryResult->value, true);
        } elseif ($daemon == 'task') {
            $query = $this->db->get_where('configs', ['key' => 'tasks_daemon_status']);
            $queryResult = $query->first_row();
            $status = json_decode($queryResult->value, true);
        } else {
            $status = false;
        }

        if (!$status) throw new Exception ('No status found for ' . $daemon . ' daemon');

        $lastReportSince = (int)$status['last_report_timestamp'];
        $lastReport = (time() - $lastReportSince) / 1000;
        $status['RUNNING'] = ($lastReport > 60) ? false : true;
        return $status;
    }
}