<?php

namespace ANDS\Registry\Suggestors;

use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Registry\Suggestors\DatasetORCIDSuggestor;
use PHPUnit\Framework\TestCase;
use ANDS\Registry\Providers\ORCID\ORCIDRecordsRepository;

class DatasetORCIDSuggestorTest extends \RegistryTestClass
{

    public function Suggest()
    {
        $data = [];
        $data['orcid'] = "https://orcid.org/0000-0002-5822-5275";
        $data['name'] = "Sebastien Mancini";
        $data['access_token'] = "zzz";
        $data['refresh_token'] = "aaa";

        $orcidRecord = ORCIDRecordsRepository::firstOrCreate("https://orcid.org/0000-0002-5822-5275", $data );
        $suggestor = new DatasetORCIDSuggestor();
        $record['person']['name']['family-name']['value'] = 'AUTParty6';
        $suggested = $suggestor->suggest($orcidRecord);
        var_dump($suggested);
    }
}
