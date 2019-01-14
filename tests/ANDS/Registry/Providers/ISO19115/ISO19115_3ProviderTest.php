<?php

namespace ANDS\Registry\Providers\ORCID;
use \ANDS\Registry\Providers\ISO19115\ISO19115_3Provider;

class ISO19115_3ProviderTest extends \RegistryTestClass
{
    /** @test */
    public function test_transform()
    {
        $record = $this->ensureKeyExist("c126e9f1-b072-9fee-6e04-d131383d9bea");
        $provider = new ISO19115_3Provider();
        $iso = $provider->process($record);
        print($iso);
    }

}
