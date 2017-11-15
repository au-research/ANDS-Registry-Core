<?php

namespace ANDS\Registry\Providers\ORCID;


class ORCIDProviderTest extends \RegistryTestClass
{
    /** @test */
    public function test_is_has_publicationDate()
    {
        $record = $this->ensureKeyExist("AODN/979e950f-5197-431b-86e1-07d8cd09e99f");
        $xml = ORCIDProvider::getORCIDXML($record, $this->mockORCIDStub());
        // TODO: fix ORCIDPRovider::getORCIDXML to not rely on XSLT and rely on REGISTRY_APP_PATH
    }

    private function mockORCIDStub()
    {
        $orcid = new ORCIDRecord();
        $orcid->orcid_id = "0000-0003-0670-6058";
        return $orcid;
    }
}
