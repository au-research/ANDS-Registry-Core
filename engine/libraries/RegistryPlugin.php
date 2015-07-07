<?php
namespace ANDS;

/**
 * Registry Plugin class
 * For the usage of extending the registry functionality
 * Usage: create a new class extends ANDS\Registry Plugin
 * and use the module_hook functionality as demonstrated in
 * sync.php@indexable_json()
 * module_hook() is defined in engine_helper
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class RegistryPlugin
{
    protected $ro;

    /**
     * Construction of the object
     * @param boolean $ro Can be passed in to provide the obj with the ro obj
     */
    public function __construct($ro = false)
    {
        if ($ro) {
            $this->injectRo($ro);
        }
    }

    /**
     * Injecting Ro
     * @param  registry_object $ro Registry Object
     * @return void
     */
    public function injectRo($ro)
    {
        $this->ro = $ro;
    }

}
