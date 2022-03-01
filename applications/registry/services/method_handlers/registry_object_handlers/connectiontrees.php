<?php
use ANDS\Registry\Providers\NestedConnectionsProvider;
use ANDS\Repository\EloquentConnectionsRepository;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(SERVICES_MODULE_PATH . 'method_handlers/registry_object_handlers/_ro_handler.php');
/**
* Conectiontree handler
* @author Liz Woods <liz.woods@ands.org.au>
* @return array
*/
class Connectiontrees extends ROHandler {

    public function handle()
    {
        //if ($this->ro->status == "DELETED")
            return [];
    }

}