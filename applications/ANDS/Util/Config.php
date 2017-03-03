<?php


namespace ANDS\Util;


class Config
{
    /**
     * Get a configuration
     * Usage: Config::get('database'), Config::get('database.registry')
     *
     * @param $name
     * @return mixed
     * @throws \Exception
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
        $filePath = BASE . "config/$name.php";

        if (!file_exists($filePath)) {
            throw new \Exception("Configuration $name does not exists");
        }

        $configuration = include(BASE . "config/$name.php");

        if ($config && array_key_exists($config, $configuration)) {
            return $configuration[$config];
        }

        return $configuration;
    }
}

if (!function_exists('config'))
{
    /**
     * Helper function for Config class
     * Usage: config('database')
     *
     * @param $name
     * @return mixed
     */
    function config($name) {
        return Config::get($name);
    }
}