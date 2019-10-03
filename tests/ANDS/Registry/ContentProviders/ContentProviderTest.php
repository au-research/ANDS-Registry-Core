<?php

namespace ANDS\Registry\ContentProviders;
use \ANDS\Registry\ContentProvider\ContentProvider;


class ISO19115_3ProviderTest extends \RegistryTestClass
{

    /** @test */
    public function test_get_correct_provider_class()
    {

        $providerClass = ContentProvider::getProvider('blah_blah_blah', 'JSONLDHarvester');
        $this->assertEquals('json', $providerClass->getFileExtension());

        $providerClass = ContentProvider::getProvider(null, null);
        $this->assertNull($providerClass);

        $providerClass = ContentProvider::getProvider('http://www.isotc211.org/2005/gmd', 'blah_blah_blah');
        $this->assertEquals('tmp', $providerClass->getFileExtension());

    }


}
