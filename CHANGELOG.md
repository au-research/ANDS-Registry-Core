# Changelog
All notable changes to this project will be documented in this file.

ANDS Research Data Registry follows a quarterly release cycle

## Release 28
* Added `RelationshipsGraphProvider` for neo4j integration

## Release 27 & Release 27.1
* Added ability to provide `app_id` and `shared_secret` for PIDs
* Updated Scholix to v3

## Release 26
* Added `ORCIDPRovider` for ORCID XML generation. 
* Updated ORCID registry wizard to use Version 2.1 of the API and produces version 2.0 ORCID XML
* Added `JSONLDProvider` to produces structured data
* Added `NLAPullBack` script as a refactor to the NLA Pull back job, this now uses the Pipeline
* Updated RIFCS vocabulary to 1.6.2
* Minor fixes includes relationship index issue, name parts, doi service...

## Release 25
* CC-2059. Removed counter odometer
* CC-2041. Added `NEWS.md` and display in Registry Dashboard. Added `CHANGELOG.md` to keep track of changes
* CC-2039. Business Logic for displaying superior and subordinate titles
* CC-2038. Updated Temporal Search logic to be inclusive of earliest and latest year
* CC-2059. Updated DataSourceAPI data source creation attribute initialisation
* CC-1818. CC-1072. Added Service Discovery Capabilities
* Added a command to export logs and rifcs to flat files 
* Fixed an indexing issue with `portalIndex` variable declaration
* Fixed an issue with `ProcessingCoreMetadata` failing the task
* Added preliminary support for RESTful API for `/api/registry/records`