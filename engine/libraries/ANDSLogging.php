<?php if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!file_exists(dirname(BASEPATH).'/vendor/autoload.php')) {
    throw new \Exception("Installation incompleted. vendor directory missing. Try running composer install");
}

require_once dirname(BASEPATH).'/vendor/autoload.php';

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ANDSLogging
{

    /**
     * Log an event using Monolog
     *
     * @param  string $event
     * @param  string $log
     * @param  string $type
     * @return void
     */
    public static function log($event, $log="activity", $type="info", $allowBot = false)
    {
        // set up the logger
        $logger = new Logger($log);
        $handler = new StreamHandler('logs/'.$log.'.log');
        $formatter = new LogstashFormatter($log, null, null, null);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        // event house keeping
        $event = static::housekeeping($event);

        $title = is_array($event) && array_key_exists('event', $event) ? $event['event'] : $event;

        if (!array_key_exists('user', $event)) {
            $event['user'] = [
                'is_bot' => false
            ];
        }

        // record the event
        if ( $allowBot === true || $event['user']['is_bot'] === false ) {
            $logger->$type($title, $event);
        }

    }

    /**
     * Housekeeping on the event
     *
     * @param  string $event
     * @return array
     */
    public static function housekeeping($event)
    {
        if (!is_array($event)) {
            return ['message' => $event, 'event' => 'unknown'];
        }

        if (!array_key_exists('event', $event)) {
            $event['event'] = 'unknown';
        }

        // populate with user data
        $event = static::populateWithUserData($event);

        // populate records with record owners
        $event = static::populateWithRecordOwners($event);

        return $event;
    }

    /**
     * Populate the event with user reference data
     *
     * @param  string $event
     * @return mixed
     */
    public static function populateWithUserData($event)
    {
        $CI =& get_instance();

        // Return the event if it's not an array structure
        if (!is_array($event)) {
            return $event;
        }

        // IP and User Agent
        if (!array_key_exists('user', $event)) {
            $event['user'] = [
                'ip' => $CI->input->ip_address(),
                'user_agent' => $CI->input->user_agent()
            ];
        }

        // Load the user library if it's not loaded already
        if (!class_exists('User') || $CI->user === null) {
            $CI->load->library('user');
        }

        // Logged In user via the User Library
        if ($CI->user && $CI->user->isLoggedIn()) {
            $event['user']['username'] = $CI->user->name();
            $event['user']['userid'] = $CI->user->localIdentifier();
            $event['user']['source'] = $CI->user->authDomain() ?: $CI->user->authMethod();
        }

        // Bot detection
        // @refactor: move to own method
        $CrawlerDetect = new CrawlerDetect();
        if ($CrawlerDetect->isCrawler($event['user']['user_agent'])) {
            $event['user']['is_bot'] = true;
        } else {
            $event['user']['is_bot'] = false;
        }

        // Referers detail
        // @refactor move to own method
        if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            $event['http_referer'] = $_SERVER['HTTP_REFERER'];
        }

        // current_url
        $event['url'] = current_url();

        return $event;
    }

    /**
     * Populate the event with relevant record owners information
     *
     * @param  array $event
     * @return array
     */
    public static function populateWithRecordOwners($event)
    {

        // record owner where record exists
        if (array_key_exists('record', $event)
            && isset($event['record']['id'])
            && isset($event['record']['data_source_id'])
        ) {
            $event['record']['record_owners'] = self::getRecordOwners($event['record']['data_source_id']);
        }

        // search event
        if ($event['event'] == 'portal_search'
            && isset($event['result']['result_dsid'])
            && count($event['result']['result_dsid']) > 0
            ) {
            $recordOwners = [];
            foreach ($event['result']['result_dsid'] as $dataSourceID) {
                $recordOwners = array_merge(
                    $recordOwners,
                    self::getRecordOwners($dataSourceID)
                );
            }
            $recordOwners = array_values(array_unique($recordOwners));
            $event['result']['record_owners'] = $recordOwners;
        }

        return $event;
    }

    /**
     * Get a list of record owner given a data source ID
     *
     * @param  string $dataSourceID
     * @return array
     */
    public static function getRecordOwners($dataSourceID)
    {
        $CI =& get_instance();
        $CI->load->driver('cache');

        $result = array();

        $cacheID = 'record-owners.ds.'.$dataSourceID;
        if ($cached = $CI->cache->file->get($cacheID)) {
            return $cached;
        }

        $CI->load->model('registry/data_source/data_sources', 'ds');
        if ($recordOwner = $CI->ds->getAttribute($dataSourceID, 'record_owner')) {
            $result = array_merge([$recordOwner], self::getParentRoles($recordOwner));
        }

        $CI->cache->file->save($cacheID, $result, 36000);
        return $result;
    }

    /**
     * Get a recursive list of all the parent role IDs of a given role ID
     *
     * @param  string $id
     * @return array
     */
    public static function getParentRoles($id)
    {
        $result = [];
        $CI =& get_instance();
        $CI->load->model('roles/roles/roles', 'roles');
        $parents = $CI->roles->descendants($id);
        foreach ($parents as $parent) {
            if ($parent->role_type_id == "ROLE_ORGANISATIONAL") {
                $result[] = $parent->role_id;
            }
        }
        return array();
    }
}
