<?php

use \ANDS\Registry\Providers\Quality\Types;

return [
    'checks' => [
        'collection' => [
            Types\CheckIdentifier::class => 'Includes an <a href="https://documentation.ands.org.au/display/DOC/Identifier">identifier</a> such as a DOI, that uniquely identifies the data',
            Types\CheckLocation::class => 'Provides access to, or information about <a href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the data being described',
            Types\CheckCitationInfo::class => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Citation+information">citation information</a> that clearly indicates how the data should be cited when reused',
            Types\CheckRights::class => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Access+rights">access rights</a> and <a href="https://documentation.ands.org.au/display/DOC/Licence">licence</a> information that specifies how the data may be reused by others',
            Types\CheckRelatedOutputs::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Related+information">related outputs</a>, such as publications, that give context to the data',
            Types\CheckRelatedParties::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Party">people and organisations</a> associated with the data to improve discovery',
            Types\CheckRelatedActivity::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Activity">projects</a> associated with the data to improve discovery and provide context',
            Types\CheckRelatedService::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Service">services</a> that can be used to access or operate on the data',
            Types\CheckSubject::class => 'Contains <a href="https://documentation.ands.org.au/display/DOC/Subject">subject</a> information to enhance discovery',
            Types\CheckCoverage::class => 'Where relevant, provides <a href="https://documentation.ands.org.au/display/DOC/Coverage">spatial and/or temporal coverage</a> information that helps researchers find data that relates to a geographical area or time period of interest'
        ],
        'activity' => [
            Types\CheckIdentifier::class => 'Includes an <a href="https://documentation.ands.org.au/display/DOC/Identifier">identifier</a> such as a PURL, that uniquely identifies the activity',
            Types\CheckLocationAddress::class => 'Includes a <a href="https://documentation.ands.org.au/display/DOC/Location">location address</a> for an activity such as a URL to a project web page',
            Types\CheckRelatedParties::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Party">people</a> associated with the activity',
            Types\CheckRelatedActivityOutput::class => 'Is connected to any related <a href="https://documentation.ands.org.au/display/DOC/Collection">collection</a> or <a href="https://documentation.ands.org.au/display/DOC/Service">service</a> that is an output of the activity',
            Types\CheckSubject::class => 'Contains <a href="https://documentation.ands.org.au/display/DOC/Subject">subject</a> information to associate an activity with collections in the same field',
            Types\CheckDescription::class => 'Includes a <a href="https://documentation.ands.org.au/display/DOC/Description">description</a> of the activity to provide context for related collections',
            Types\CheckExistenceDate::class => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Existence+dates">existence dates</a> for the activity to allow users to narrow their search by date'
        ],
        'party' => [
            Types\CheckIdentifier::class => 'Includes an <a href="https://documentation.ands.org.au/display/DOC/Identifier">identifier</a> such as an ORCID, that uniquely identifies the party',
            Types\CheckLocationAddress::class => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Location">contact details</a> for a person or organisation',
            Types\CheckRelatedActivity::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Activity">activities</a> associated with the party',
            Types\CheckRelatedCollection::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Collection">collections</a> associated with the party'
        ],
        'service' => [
            Types\CheckIdentifier::class => 'Includes an <a href="https://documentation.ands.org.au/display/DOC/Identifier">identifier</a> such as a handle, that uniquely identifies the service',
            Types\CheckLocation::class => 'Provides access to, or information about <a href="https://documentation.ands.org.au/display/DOC/Location">how to access</a>, the service being described',
            Types\CheckDescription::class => 'Includes a <a href="https://documentation.ands.org.au/display/DOC/Description">description</a> of the service for potential users',
            Types\CheckRights::class => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Access+rights">access rights</a> and <a href="https://documentation.ands.org.au/display/DOC/Licence">licence</a> information that specifies how the service may be reused by others',
            Types\CheckRelatedInformation::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Related+information">related information</a> that supports use of the service, such as additional protocol information',
            Types\CheckRelatedParties::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Party">parties</a> associated with the service',
            Types\CheckRelatedCollection::class => 'Is connected to <a href="https://documentation.ands.org.au/display/DOC/Collection">collections</a> that can be accessed through, or acted upon by, the service',
            Types\CheckSubject::class => 'Includes <a href="https://documentation.ands.org.au/display/DOC/Subject">subject</a> terms that describe the research focus of the service',
        ]
    ]
];