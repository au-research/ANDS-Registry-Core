<?php


namespace ANDS\Util;


class Config
{
    /**
     * Cached data.
     *
     * @var array
     */
    protected static $data = [];

    /**
     * Get a configuration
     * Usage: Config::get('database'), Config::get('database.registry')
     *
     * @param $name
     * @return mixed
     */
    public static function get($name)
    {
        $config = null;
        // get name.config if exists
        if (strpos($name, ".") > 0) {
            $parts = explode('.', $name);
            $name = $parts[0];
            $config = $parts[1];
        }

        // name should now be the config name
        $filePath = dirname(__DIR__) . "/../../config/$name.php";

        if (!file_exists($filePath)) {
            // TODO: log error
            return null;
        }

        // check if the config is already statically cached during run time
        if (!array_key_exists($name, self::$data)) {
            self::load($name);
        }

        // fetch the configuration that is already loaded (should)
        $configuration = self::$data[$name];

        // TODO cater for multiple dot app.config.cache.something
        if ($config && array_key_exists($config, $configuration)) {
            return $configuration[$config];
        }

        return $configuration;
    }

    /**
     * Load the configuration file into static caching
     *
     * @param $name
     */
    protected static function load($name)
    {
        $configuration = include(dirname(__DIR__) . "/../../config/{$name}.php");
        self::$data[$name] = $configuration;
    }

}

if (!function_exists('config')) {
    /**
     * Helper function for Config class
     * Usage: config('database')
     *
     * @param $name
     * @return mixed
     */
    function config($name)
    {
        return Config::get($name);
    }
}