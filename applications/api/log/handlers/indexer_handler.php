<?php
namespace ANDS\API\Log\Handler;

use \Exception as Exception;

/**
 * Handles registry/grants
 * getGrants API
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class IndexerHandler extends Handler
{
    private $supported = array('rda', 'doi');

    function handle() {
        // return ($this->params);
        if (!$this->params['identifier']) {
            return $this->listSupported();
        } else {
            if (in_array($this->params['identifier'], $this->supported)) {
                $module = $this->params['identifier'];
                $class_name = 'ANDS\API\Log\Indexer\\'.$this->params['identifier'].'Indexer';
                if (class_exists($class_name)) {
                    $indexer = new $class_name($this->params);
                    return $indexer->handle();
                } else {
                    throw new Exception('class '.$class_name.' not found!');
                }
            }
        }
    }

    private function rda() {
        if (!$this->params['method_module']) {
            // list rda core health
        } else {
            switch ($this->params['method_module']) {
                case 'listDates': return 'date'; break;
            }
        }
        return $this->params;
    }

    private function listSupported() {
        return $this->supported;
    }
}