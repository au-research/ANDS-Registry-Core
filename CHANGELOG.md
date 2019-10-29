# Changelog
All notable changes to this project will be documented in this file.

## Release 34
* Enhance the RDA EndNote Export process:
  * Export collections of type software as 'computer program' 
  * Export authors in the same order as they are displayed in the title bar in RDA 
  * Maintain the original case of the input characters when generating .ris file
  * Decode HTML character coding prior to writing the content to .ris file 
* Improve how authors under the record title in RDA view page are displayed:
  * Where citation metadata exist for datasets:
    * Display citation contributors as related researchers under the record title ordered by citation sequence number
    * Remove the '(Contributor)' relationship displayed after citationMetadata contributors in the RDA title bar
  * For collections, grants and activities, order related parties in the title bar and description, by Principal Investigators first (display name A-Z), followed by all other related parties in alphabetical order (display name A-Z).
  * For an activity record, remove 'Participant' relationship for all researchers and only specify the Principal Investigators.
  * Where a party displayed in the title bar has duplicates via identifier, attempt to display and link to the contributor's party before others 

## Release 33
* Fix issue causing imports to fail due to the number of impacted records.

## Release 32
* Optimisation of CSS to improve page load times.
* Improvements to RIF-CS to Schema.org mapping.

## Release 31
* Remove Google share functionality 

## Release 30.1
* Fix bug where the type icon is not displayed for service records 
* Ensure collection type is indexed correctly 
* Optimisation of the Similar Datasets pools and queries 

## Release 30

* Addition of a ‘Type’ facet in the collection search that allows users to filter by software and data.
* New software icon and label in the title bar on the RDA view page.
* Change the ‘Access the Data’ button label to ‘Access the software’ in a software record view page.
* Addition of a ‘Software’ theme page and associated Explore menu item.
* In the Contributor Page, add ‘Software’ to the organisation’s list of contributed records
* If alternative name is empty or is not used in the record, do not display 'Also known as:' under the record title in RDA record view page
* Performance improvements made to the indexing, querying and caching functionality of the graph to improve response times and stability.
* Functionality that allows users to extract OGC services from within their metadata feeds will be added to the Data Source Dashboard import options.
  Upon running the process, the system will attempt to find OGC service ('wms', 'wfs', 'ogc', 'wcs', 'wps', 'wmts' & 'ows') URLs within the current published collections within the datasource. Where the system can successfully communicate with a discovered service via a 'getCapabilities' request, the system will generate a RIF-CS record and import it into the data source as a draft record. Users can then review and edit the generated records before publishing them.
* New OAI-PMH service


## Release 29
* Updated social login integrations
* Updated AAF/RapidConnect integrations
* Replacement of Metadata Quality Report with new Metadata Content Report
* Single client minting for DOI

## Release 28.1
* Minor bug fixes and feature enhancements

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
