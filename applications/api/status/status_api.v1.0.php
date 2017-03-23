<?php
/**
 * Class:  Status API
 * @author: Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */

namespace ANDS\API;

use ANDS\Util\Config;
use \Exception as Exception;


class Status_api
{
    public function __construct()
    {
        $this->ci = &get_instance();
        $this->db = $this->ci->load->database('registry', true);
    }

    /**
     * Handling api/status
     * @param array $method
     * @return array|bool|mixed
     */
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

    /**
     * Handling api/status/:module
     * @param $module
     * @return bool|mixed
     * @throws Exception
     */
    private function reportFor($module)
    {
        if ($module == 'harvester' || $module == 'task') {
            return $this->getDaemonStatus($module);
        } else if($module == 'solr') {
            return $this->getSOLRStatus() ? array_merge($this->getSOLRStatus(), ['RUNNING'=>true]) : ['RUNNING'=>false];
        }
        return false;
    }

    /**
     * Handle for api/status/
     * @return array
     * @throws Exception
     */
    private function report()
    {
        $result = [
            'database' => $this->getDatabaseStatus(),
            'harvester' => $this->getDaemonStatus('harvester'),
            'task' => $this->getDaemonStatus('task'),
            'solr' => $this->getSOLRStatus() ? array_merge($this->getSOLRStatus(), ['RUNNING'=>true]) : ['RUNNING'=>false]
        ];
        return $result;
    }

    private function getDatabaseStatus()
    {
        $capsule = new \Illuminate\Database\Capsule\Manager;
        $config = Config::get('database');
        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = true;
            try {
                $capsule->getConnection('default');
            } catch (\Exception $e) {
                $result[$key] = false;
            }
        }

        return $result;
    }

    /**
     * Handle for api/status/:daemon
     * Supported daemon are harvester|task
     * @param $daemon
     * @return bool|mixed
     * @throws Exception
     */
    private function getDaemonStatus($daemon)
    {
        $status = false;
        if ($daemon == 'harvester') {
            $query = $this->db->get_where('configs', ['key' => 'harvester_status']);
            $queryResult = $query->first_row();
            if ($queryResult) {
                $status = json_decode($queryResult->value, true);
            }
        } elseif ($daemon == 'task') {
            $query = $this->db->get_where('configs', ['key' => 'tasks_daemon_status']);
            $queryResult = $query->first_row();
            if ($queryResult) {
                $status = json_decode($queryResult->value, true);
            }
        } else {
            $status = false;
        }

        if (!$status) {
            return [
                'msg' => 'no status available',
                'lastReport' => 'never',
                'RUNNING' => false
            ];
        }

        $lastReportSince = (int) $status['last_report_timestamp'];
        $lastReport = (time() - $lastReportSince);

        $status['lastReport'] = $lastReport .' seconds ago';
        $status['RUNNING'] = ($lastReport > 60) ? false : true;

        return $status;
    }

    /**
     * Returns the all core status for SOLR
     * Hitting admin/cores?action=status
     * @return mixed|bool
     */
    private function getSOLRStatus()
    {
        $url = get_config_item('solr_url') . 'admin/cores?action=status&wt=json';
        $contents = @file_get_contents($url);
        if ($contents) {
            $contents = json_decode($contents, true);
        }
        return isset($contents['status']) ? $contents['status'] : false;
    }
}