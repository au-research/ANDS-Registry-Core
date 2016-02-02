<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Registry Object model for a single registry object
 *
 * @author  Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class _ro
{

    //object properties are all located in the same array
    public $prop;
    private $useCache;

    /**
     * _ro constructor.
     * Constructor of this object, basically calls the init
     *
     * @param       $id
     * @param array $populate
     * @internal param $useCache
     */
    function __construct($id, $populate = array('core'))
    {
        $this->useCache = false;
        //populate the property as soon as the object is constructed
        $this->init($id, $populate);
    }

    /**
     * Initialize a registry object
     *
     * @param  int   $id       registry object id
     * @param  array $populate a list of attributes to populate, default to just core
     * @return void
     */
    function init($id, $populate = array('core'))
    {
        $this->prop = array(
            'id' => $id
        );
        $this->fetch($populate);
    }

    /**
     * Magic function to get an attribute, returns property within the $prop array
     *
     * @param  string $property property name
     * @return property result
     */
    public function __get($property)
    {
        if (isset($this->prop[$property])) {
            return $this->prop[$property];
        } else {
            return false;
        }
    }

    /**
     * Magic function to set an attribute
     *
     * @param string $property property name
     * @param string $value    property value
     */
    public function __set($property, $value)
    {
        $this->prop[$property] = $value;
    }

    /**
     * Construct the API URL based on the amount of data required
     *
     * @todo add support for setting a special API key from the configuration
     * @return string $url
     * @internal param array $params a list of parameter to query
     */
    public function construct_api_url()
    {
        $url = base_url() . 'registry/services/api/registry_objects/' . $this->id . '/';
        return $url;
    }

    /**
     * Fetch data from the Registry API
     *
     * @param  array $params list of parameters to fetch
     * @todo  ERROR HANDLING
     * @return void
     */
    public function fetch($params = array('core'))
    {
        //get the URL
        $url = $this->construct_api_url($params);
        $this->prop['api_url'] = $url;
        $this->prop['message'] = "OK";
        $this->prop['fromCache'] = $this->useCache;
        $this->prop['status'] = false;
        //try and get it from cache
        $cache_id = 'ro-portal-' . $this->id;
        $ci =& get_instance();
        $ci->load->driver('cache');

        //refresh the cache when required
        if ($ci->input->get('refresh')) {
            $ci->cache->file->delete($cache_id);
        }

        if ($this->useCache) {
            if (!$content = $ci->cache->file->get($cache_id)) {
                //not in the cache, get it and save it
                $content = @file_get_contents($url);
                $contentArray = json_decode($content, true);
                if ($contentArray['status'] == 'success') {
                    $ci->cache->file->save($cache_id, $content, 3600);
                }
            }
        } else {
            $content = @file_get_contents($url);
        }

        //Fetch the data and populate as per the result
        $content = json_decode($content, true);
        $this->prop['status'] = $content['status'];
        if ($content['status'] == 'success') {
            foreach ($params as $par) {
                if (isset($content['message'][$par]) && is_array($content['message'][$par])) {
                    foreach ($content['message'][$par] as $attr => $val) {
                        $this->prop[$par][$attr] = $val;
                    }
                }
            }
        } else {
            $this->prop['message'] = $content['message'];
        }
    }

    /**
     * Returns the stats of this registry object via the DB
     * Creates an empty one in case there's no stat
     * NB See also the _update_citation_counts_in_portal_database method
     * of etc/misc/python/citation_services/services/TRDCI.py
     * for the one other place in the code where rows are added
     * to the record_stats table.
     *
     * @return array
     */
    public function stat()
    {
        $ci =& get_instance();
        $db = $ci->load->database('portal', true);

        $result = $db->get_where('record_stats', array('ro_id' => $this->core['id']));
        if ($result->num_rows() == 0) {
            //create if not exist
            $data = array(
                'ro_id' => $this->core['id'],
                'ro_slug' => $this->core['slug']
            );
            $db->insert('record_stats', $data);
            $result = $db->get_where('record_stats', array('ro_id' => $this->core['id']));
        }
        $result_array = $result->result_array();
        return $result_array[0];
    }

    /**
     * Returns the citation of the record
     * @param string $class
     * @param string $type
     * @return string
     */
    public function cite($class = 'endnote', $type = 'text')
    {
        if ($class == 'endnote') {
            if ($type == 'text') {
                return '';
            } elseif ($type == 'link') {
                return base_url('registry_object/' . $this->core['id'] . '/cite/endnote');
            }
        } else {
            return '';
        }
        return '';
    }

    /**
     * Record an event
     *
     * @param  string $event view|cite|access
     * @param int     $value
     */
    public function event($event = 'NO DEFAULT', $value = 1)
    {
        $validEvents = array('viewed', 'accessed');
        if ($this->stat() && in_array($event, $validEvents)) {
            //make sure there's a stat instance
            // cited is handled by the registry from 30/03/2015!!!
            $ci =& get_instance();
            $db = $ci->load->database('portal', true);
            $db->where('ro_id', $this->core['id']);
            if ($event == 'viewed') {
                $db->set('viewed', 'viewed +' . $value, false);
            } else {
                if ($event == 'accessed') {
                    $db->set('accessed', 'accessed + ' . $value, false);
                }
            }
            $db->update('record_stats');
        }
    }

    /**
     * @param mixed $useCache
     */
    public function setUseCache($useCache)
    {
        $this->useCache = $useCache;
    }

}