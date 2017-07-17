# Changelog
All notable changes to this project will be documented in this file.

ANDS Research Data Registry follows a quarterly release cycle

## [Unreleased](https://github.com/au-research/ANDS-ResearchData-Registry/compare/master...develop)
* Added a command to export logs and rifcs to flat files 
* Fixed an indexing issue with `portalIndex` variable declaration
* Added `CHANGELOG.md` and display in Registry Dashboard
* Business Logic for displaying superior and subordinate titles
* Updated Temporal Search logic to be inclusive of earliest and latest year

## Release 24 -  2017-07-05
### Added
* Enhance CMD DOI request events to capture more transaction information

### Changed
* Fix the issue with the display of some special characters in RDA
* Update subject resolution in RDA such that only language codes with correct correct types (ISO 639) are resolved to their string names
* Implement a fix to prevent duplicate records from displaying as additional related objects in an Activity view page
* Fix a validation error bug when editing a record with more than 30 `relatedObjects/relatedInfo`
* Implement a fix to the the DOI manual minting service to ensure updates are processed only when both URL and XML pass validation

## Release 23.1 - 2017-05-10
### Added
* Enhance the RDA algorithm used to identify and suggest similar datasets - this includes:
    * Identification of datasets that have a 'hasAssociationWith' relationship to the dataset being viewed
    * Using record view events to infer relationships between collections.
Excluding datasets from Similar datasets if they are already displayed in the record view (ie. Related datasets)
* Implement OAI-PMH export of Scholix metadata records

### Changed
* Enhance RDA link checker to handle URLs that contain UTF-8 encoded characters
* Fix an issue with My DOIs admin interface where changes to a clients configuration does not propagate to DataCite.
* Add the distinct text 'TEST_DOI' to test DOI identifiers in order to make them easily recognisable. 

## Release 23 - 2017-04-11
This release is focused on the migration of ANDS services infrastructure to a new service provider with some minor changes to the Research Vocabularies Australia

### Changed
* Performance Enhancement and Bugfixes

## Release 22.1 - 2017-02-09
### Added
* Grants & Projects: Display 'View all <xx> related researchers' for grants with more than 5 related parties
* RDA View Page: Fix bug where Schema.org itemType was set to 'Dataset' for all class types

### Changed
* RDA Duplicate Identifiers : Re-implement the merging of relationships for records with duplicate identifiers. This feature did not work since R19
* Theme Page: Enable multiple badges to be displayed on a view page in RDA
* Harvester error log: Harvester should not throw 'Harvest error' if no record is found in the feed

## Release 22 - 2016-12-07
### Changed
* Update the ordering of related people in the title bar of collection records so that principal investigators are displayed first
* Refactor ANDS Registry import and ingest process into smaller, robust, and testable components
