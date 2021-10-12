<?php


namespace ANDS\File;


use Symfony\Component\Finder\Finder;

class File
{
    public static function storage($name = 'primary')
    {
        $storage = new Finder();
        return $storage->in(__DIR__)->directories();
    }
}