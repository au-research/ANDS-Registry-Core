<?php
namespace ANDS\API\Log\Indexer;
use ANDS\API\Log\Indexer as Indexer;
use \Exception as Exception;

class rdaIndexer extends Indexer{

    function handle() {
        if (!$this->params['method_module']){
            return $this->detail();
        } else {
            $method = $this->params['method_module'];
            if (method_exists($this, $method)) {
                return $this->$method();
            } else {
                throw new Exception('Method not supported: '. $method);
            }
        }
    }

    /**
     * api/log/indexer/rda/dates
     * @return [type] [description]
     */
    private function dates() {
        $dates = readDirectory('engine/logs/portal');
        foreach ($dates as &$date) {
            $date = str_replace("log-portal-", "", $date);
            $date = str_replace(".php", "", $date);
        }
        natsort($dates);
        $dates = date_range(reset($dates), end($dates), '+1day', 'Y-m-d');
        return $dates;
    }

    private function detail() {
        return 'rda';
    }

}