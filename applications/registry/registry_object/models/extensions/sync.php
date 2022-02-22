<?php use ANDS\Repository\RegistryObjectsRepository;
      use ANDS\Registry\Providers\RelationshipProvider;

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sync_extension extends ExtensionBase{

    private $party_one_types = array('person','administrativePosition');
    private $party_multi_types = array('group');


	function __construct($ro_pointer){
		parent::__construct($ro_pointer);
	}

	/**
	 * Do an enrich and commit, if full is provided, do add relationships and update quality metadata as well
	 * With great power comes great responsibility
	 * @param boolean $full
	 * @return boolean/string [if it's a string, it's an error message]
	 */
	function sync($full = true, $conn_limit=20){
	    try {
	        $record = RegistryObjectsRepository::getRecordByID($this->ro->id);
	        \ANDS\Registry\Importer::instantSyncRecord($record);
        } catch (Exception $e) {
	        return "error: ". $e;
        }
        return true;
	}

    // todo revisit cache in CI
    function _dropCache()
    {
        $api_id = 'ro-api-'.$this->ro->id.'-portal';
        $portal_id = 'ro-portal-'.$this->ro->id;
        $ci =& get_instance();
        $ci->load->driver('cache');
        try{
	        if ($ci->cache->file->get($api_id)) $ci->cache->file->delete($api_id);
	        if ($ci->cache->file->get($portal_id)) $ci->cache->file->delete($portal_id);
        }
        catch(Exception $e){

        }
    }
}