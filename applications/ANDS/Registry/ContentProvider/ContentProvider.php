<?php
/**
 * Created by PhpStorm.
 * User: leomonus
 * Date: 23/9/19
 * Time: 11:49 AM
 */

namespace ANDS\Registry\ContentProvider;


class ContentProvider
{
    static public function obtain($providerType) {

        // not a good list but good start

        $config = [
            'schema' => [
                'JSONLDHarvester' => [
                    'provider' => \ANDS\Registry\ContentProvider\JSONLD\JSONLDContentProvider::class,
                    'namespace' => 'schema.org',
                ],
                'http://www.isotc211.org/2005/gmd' => [
                    'provider' => \ANDS\Registry\ContentProvider\ISO\ISO191153ContentProvider::class,
                    'namespace' => 'dynamic',
                ],
                'CSWHarvester' => [
                    'provider' => \ANDS\Registry\ContentProvider\ISO\ISO191153ContentProvider::class,
                    'namespace' => 'http://www.isotc211.org/2005/gmd',
                ]
            ]
        ];

        if(!isset($config['schema'][$providerType]))
            return null;

        $className = $config['schema'][$providerType]['provider'];

        return $className;
    }
}