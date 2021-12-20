<?php

namespace ANDS\Registry\Events;

use ANDS\Registry\Events\Event\DataSourceUpdatedEvent;
use ANDS\Registry\Events\Listener\InsertDataSourceLog;
use Exception;
use ReflectionClass;

class EventServiceProvider
{
    private static $config = [
        DataSourceUpdatedEvent::class => [
            InsertDataSourceLog::class
        ]
    ];

    public static function getShortNames() {
        return collect(self::$config)->keys()->map(function ($event) {
            $reflect = new ReflectionClass(new $event);
            return $reflect->getShortName();
        })->toArray();
    }

    public static function getShortNameMapping() {
        return collect(self::$config)->map(function ($value, $key) {
            $reflect = new ReflectionClass(new $key);
            return $reflect->getShortName();
        })->flip()->toArray();
    }

    public static function resolveEvent($shortName) {
        $shortNameMapping = self::getShortNameMapping();

        if (!array_key_exists($shortName, $shortNameMapping)) {
            return null;
        }

        return new $shortNameMapping[$shortName];
    }

    /**
     * @throws Exception
     */
    public static function dispatch(Event $event) {
        $listeners = static::getListeners($event);

        monolog([
            'event' => 'event_triggered',
            'event_type' => get_class($event),
            'event_listener' => implode(', ', $listeners)
        ], "app");

        foreach ($listeners as $listener) {
            try {
                $listenerObj = new $listener;
                $listenerObj->handle($event);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }

    public static function getListeners(Event $event) {
        $clazz = get_class($event);
        return array_key_exists($clazz, static::$config) ? static::$config[get_class($event)] : [];
    }
}