<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 23/9/19
 * Time: 11:49 AM
 */

namespace ANDS\Registry\ContentProvider;
use \ANDS\Util\Config;
use ReflectionClass;

class ContentProvider
{
    static public function getProvider($providerType, $harvestMethod) {

        // This class will attempt to use reflection to obtain a content provider for the given content type or harvest method
        // in that order
        $providerConfig = Config::get('app.content_providers');
        try{
            if($providerType != null and isset($providerConfig[$providerType])){
                $class = new ReflectionClass($providerConfig[$providerType]);
                return $class->newInstanceArgs();
            }
            if ($harvestMethod != null and isset($providerConfig[$harvestMethod])){
                $class = new ReflectionClass($providerConfig[$harvestMethod]);
                return $class->newInstanceArgs();
            }
        }
        catch (\Exception $e)
        {
            return null;
        }

        return null;
    }
}