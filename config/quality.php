<?php

use \ANDS\Registry\Providers\Quality\Types;

return [
    'checks' => [
        'collection' => [
            Types\CheckIdentifier::class,
            Types\CheckLocation::class,
            Types\CheckCitationInfo::class,
            Types\CheckRights::class,
            Types\CheckRelatedOutputs::class,
            Types\CheckRelatedParties::class,
            Types\CheckRelatedActivity::class,
            Types\CheckRelatedService::class,
            Types\CheckSubject::class,
            Types\CheckCoverage::class
        ],
        'activity' => [
            Types\CheckIdentifier::class,
            Types\CheckLocationAddress::class,
            Types\CheckRelatedParties::class,
            Types\CheckRelatedActivityOutput::class,
            Types\CheckRelatedService::class,
            Types\CheckSubject::class,
            Types\CheckDescription::class,
            Types\CheckExistenceDate::class
        ],
        'party' => [
            Types\CheckIdentifier::class,
            Types\CheckLocationAddress::class,
            Types\CheckRelatedActivity::class,
            Types\CheckRelatedCollection::class
        ],
        'service' => [
            Types\CheckIdentifier::class,
            Types\CheckLocation::class,
            Types\CheckDescription::class,
            Types\CheckRights::class,
            Types\CheckRelatedInformation::class,
            Types\CheckRelatedParties::class,
            Types\CheckRelatedCollection::class,
            Types\CheckSubject::class
        ]
    ]
];