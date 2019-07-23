<?php


namespace ANDS\File;


use ANDS\Util\Config;

class Storage
{
    private $driver;
    private $path;

    /**
     * Storage constructor.
     * @param $storage
     */
    public function __construct($storage)
    {
        $this->driver = $storage['driver'];
        $base = __DIR__ .'/../../../';
        $this->path = rtrim($base. $storage['path'], '/') .'/';
    }

    public static function disk($name)
    {
        $storage = Config::get('app.storage');
        return new static($storage[$name]);
    }

    public function get($fileName)
    {
        return file_get_contents($this->getPath($fileName));
    }

    public function put($fileName, $fileContent)
    {
        return file_put_contents($this->path . $fileName, $fileContent);
    }

    public function getPath($fileName)
    {
        return $this->path . $fileName;
    }

}