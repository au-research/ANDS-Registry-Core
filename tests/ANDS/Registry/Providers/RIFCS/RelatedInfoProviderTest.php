<?php

namespace ANDS\Registry\Providers\RIFCS;

use ANDS\Registry\Providers\RIFCS\RelatedInfoProvider;

class RelatedInfoProviderTest extends \RegistryTestClass
{


    public function test_related_info(){
        $record = $this->ensureKeyExist("C1_46");
        $related_info_search = RelatedInfoProvider::getIndexableArray($record);
        $this->assertEquals("Example party 3 http://nla.gov.au/nla.party-P1_46" , $related_info_search["related_info_search"][0]);
    }

}
