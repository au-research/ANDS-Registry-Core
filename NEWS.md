
## Release 40
**Research Data Australia**
* Add meta tags to head element of collection view pages to support Altmetrics.

## Release 39
**DOI Service**
* Decommission My DOIs. Manual minting via My DOIs will no longer be available through the RDA Registry. All manual minting will be performed through DataCite Fabrica.
**Research Data Australia**
* Add new harvest method that can crawl asynchronously loaded JSON-LD.
* Add new harvest method to support the harvesting of grant information from the ARC Data Portal.

## Release 38
* Privacy Policy update.
* Restore the My RDA Google login button.
* Implement support for crosswalking between the same harvest metadata prefix.

## Release 37
* New harvest method to support harvesting from APIs that implement the US Government Project Open Data API.
* New harvest method to support harvesting from the Australian Research Council Grants portal API.
* Increase allocated processing time for Scholix metadata to prevent process from failing.
* Fix bug where cache for graph visualisation is not refreshed after record content is updated.

## Release 36 (12 March 2020)
**Research Data Australia**
* Create resolvable DOI links using https://doi.org instead of http://dx.doi.org to conform with the DOI display guidelines 
* Update RDA Registry help links to point to the new documentation.ardc.edu.au pages 
* Fix the issue where RDA Registry users could not save changes to records using TinyMCE WYSIWYG editor

**DOI Service**
* Updates to support DataCite Fabrica account authentication changes 

**Handle Service**
* Improve the handle service response time by simplifying the mint and update process

## Release 35
Release 35 of ARDC Online Services is scheduled for implementation on **Tuesday, 17 December 2019 between 8am and 12pm AEDT**.
This release implements a number of enhancements and bug fixes to Research Data Australia (RDA) harvester and indexing of subjects. 

**Research Data Australia**

* Enhancements to the harvester to support chunked transfer encoding via HTTP v1.1 
* Addition of new harvest method to support harvesting from MAGDA API 
* Fix bug where records with special characters in subject label are failing to index due to error thrown during subject resolution 
* Fix bug where RDA Registry pre populates PURE Harvest params with CSW values Fix issue where harvest last run date on the Dashboard is shown in the past 

## Release 34

Release 34 of ARDC Online Services is scheduled for implementation on **Tuesday, 29 October 2019 between 8am and 12pm AEDT**. This release implements a number of enhancements and bug fixes to Research Data Australia (RDA) portal, RDA Registry and Research Vocabularies Australia (RVA). Below is the list of all the changes planned for this release.

**Research Data Australia**

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

**RDA Registry**

* Update the RDA Harvester to support Elsevier Pure 
* Modify the harvest/import process to allow crosswalks and imports to re-rerun on previously harvested records rather than to rerun the entire harvest when the crosswalk is updated
* Add support for the harvesting of JSON content via the 'GET Harvester' method (e.g. CKAN) 
* Addition of new harvest method 'JSONLD' for the harvesting and import of [schema.org](http://schema.org/) JSON-LD

## Release 31

Release 31 of ARDC Online Services is scheduled for implementation on 2 April 2019, between 8am and 12pm AEDT. This release implements a number of enhancements and bug fixes to the Research Data Australia Registry and Research Vocabularies Australia. Below is the list of all the changes planned for this release.

**Research Data Australia (RDA)**

* Remove Google share functionality (CC-2461)

**Research Data Australia Registry**

* Allow users to auto populate the add registry object form by providing an OGC service URL (CC-1818)
* Enable the registry to store and expose via OAI-PMH, multiple metadata formats for records (CC-1818)

**DOI Service**

* Migrate test accounts to the DOI Fabrica Test system (CC-2211)

## Release 30

ANDS Online Services Release 30 is due for implementation on Tuesday, 4 December 2018.  Please find below the summary of all Release 30 changes with an overview of who, what and how the changes will impact our users.

**Research Data Australia (RDA)**

* Promote visibility and enhance display of collection type 'software' in RDA
* Fix bug where citationMetadata version is not included in citation text.
* Hide 'Also known as' label under the record title if an alternative name is empty or not used
* Optimise relationship graph for better performance and stability.

**RDA Registry**

* Add service discovery workflow into Data Source Dashboard import options
* Rewrite of OAI-PMH service to improve performance and fix memory issues.

## Release 29

Release 29 of ANDS Online Services is scheduled for implementation on **3 October 2018**, between 8am and 12pm AEST. This release implements a number of enhancements to Research Data Australia (RDA), DOI Service, IGSN Service, and Research Vocabularies Australia (RVA). Below is the list of all the changes planned for this release.

**Research Data Australia (RDA)**

* Update Social Login API Integration

**RDA Registry**

* Replacement of Metadata Quality Report with new Metdata Content Report (CC-2184)
* Upgrade the ORCID widget to use V2.0 of the ORCID API (CC-2243)

**DOI Service**

* Implement a single client account for both Production and Test minting (CC-2211)

## Release 28.1

Service Release 28.1 of ANDS Online Services is scheduled for implementation on 7 August 2018 from 8:00am - 12:00pm AEST. This release implements a number of enhancements to  Research Data Australia (RDA), IGSN Service and Research Vocabularies Australia (RVA). Below is the list of all the changes planned for this release.


**Research Data Australia (RDA)**

* Only display external link icon on the 'Access the data' button when linking to a page or location outside RDA (CC-2238)
* Enhancements to relationship graph on the record view page:
    * add an icon which links to a help document (CC-2235)
    * add zoom controls (CC-2234)
* Implementation of a more user friendly display of the record's contact information within the record view page (CC-2233)
* Publish an updated RDA privacy policy (CC-2209)

**RDA Registry**

* Add user agent to harvest requests to ensure harvest endpoints requiring a user agent can be harvested from (CC-2244)


## Release 28

ANDS Online Services Release 28 is due for implementation on Wednesday, 4 July 2018.  Please find below the summary of all R28 changes with an overview of who, what and how the changes will impact our users. 

**Research Data Australia (RDA) changes**

* Integrate Relationship Graph to RDA view pages
* Update the RDA party view page to ensure that the electronic address is displayed when provided in RIF-CS
* Change the label 'Go To Data Provider' to 'Access Data' in RDA view page and update the display to emphasise a clickable button
* Properly index collections without licence type under 'Other' rather than 'Unknown' 
* Enhancements to the Broken Link tool
* Fix bug in query for 'Last 5 Datasets' section on Contributor pages

**DOI Service**

* New Functionality: Transfer of DOI ownership
* Assignment of individual DOI prefix per client
* Implementation of a single Test account for all clients
* Removal of the client ID from DOIs
* Implementation of an API endpoint that returns the status of the DOI 
* Validation of the response code/message received from DataCite after client creation or update

## Release 27.1

Release 27.1 of ANDS Online Services is scheduled for implementation on Thursday, 3 May 2018 from 8am to 12pm. During the implementation, the following services will be unavailable:

* Research Data Australia (RDA)
* Research Vocabularies Australia (RVA)
* ANDS Handle Service
* ANDS DOI Service

The following services are not impacted by the release implementation:

* DOI resolution
* Handle resolution
* RVA Editor (PoolParty)

Below is the list of changes included in this service release:

**Research Data Australia (RDA) changes**

* Fix an issue where Export to EndNote fails when one or more records being exported do not contain an author
* Update Scholix export functionality to use v3.0 of the Scholix schema

**Research Vocabularies Australia (RVA) changes**

* Fix broken list of formats in SISSVoc HTML pages
* Remove unusable 'more like this' and related search buttons in SISSVoc HTML pages
* CMS: Enhance the Vocabs CMS modal dialog to be able to handle user-source SISSVoc endpoints

**Handle Service**

* Extend the handle service API to enable authentication via shared secret instead of IP address
* Enhance handle service logging mechanism by ensuring that an appID is captured during minting

Please take note that a soft release to Demo will be implemented on 2 May 2018 between 8am and 5pm.

## Release 27

Release 27 of ANDS Online Services is scheduled for implementation on 7 March 2018. This release implements a number of enhancements to the Research Vocabularies Australia (RVA), DOI Service and and a bug fix and enhancement to Research Data Australia.

**Research Data Australia**

* Update of the broken link on RDA About page's 'Become a contributor' link 
* Removal of 'Contact information' heading in RDA record display when contact information is not available
 
**DOI Service**

* Implementation of additional checks after each request made to DataCite to ensure correct handling of response

**Research Vocabularies Australia**

* Complete refactor of the RVA backend and publishing workflow. Notable changes include:

***URLs***

* The canonical URL of the view page of a vocabulary has changed to be ID-based.
* The URL of the resource IRI resolution service is changing. The users of this service will be contacted in a separate communication.
* Slug generation has been improved. (Slug generation is applied to vocabulary and version titles, and is used, for example, in the URLs of generated SPARQL and LDA endpoints.) If a vocabulary or version title contains non-ASCII characters, slug generation will convert them into ASCII equivalents. For example, a version title "你好" will be converted into "ni-hao".

***CMS***

* A subset of HTML elements is now officially supported and used in certain metadata fields: vocabulary description and note, and version note. An embedded HTML editor is provided; it is also possible to edit the raw HTML.
* The related entity dialog has been substantially revised, to reflect the raising of related entities to "first class" objects.
* The functionality to add a related vocabulary that is within RVA has been separated into a separate dialog.
* When editing an existing published version, there is now a switch to request a one-off re-harvest/re-import/re-publication of that version, irrespective of its status as current/superseded. The switch is off by default.
* Removal of a file access point from a published version is now supported. 
* When adding an access point to a version, an access point format can now only be entered for access point type = File (e.g., not for API/SPARQL)

***Vocabulary View Page***

* The popup for a related entity now includes its websites (URLs) and identifiers.
* In the related entity panel's list of "More vocabularies related to xxx", the full list of related vocabularies is shown, not just one.


## Release 26

Release 26 of ANDS Online Services is scheduled for implementation on 6 December 2017. This release implements a number of Research Data Australia (RDA), DOI Service, Research Vocabularies Australia (RVA) and ORCID-RDA Integration service fixes and enhancement requests received through the ANDS Service Desk. New RIF-CS vocabulary terms will also be introduced in RIF-CS v1.6.2.

**Research Data Australia**

* Correct the display of Contact Information for records having multiple location addresses 
* Fix the issue where inaccurate records are returned in RDA Suggested Datasets due to incorrect subjects pool query
* Review and update Schema.org mapping for Collections and Services 
* Refactor the NLA pullback job to ensure that new NLA identifiers used in RDA records are retrieved from NLA and published in a timely manner 
* Fix the relationship index so that the correct authors are identified when exporting collections to EndNote
* Fix the ordering of AKA name parts

**RDA Registry**
* Allow data source administrators (DSAs) to customise the default CSW harvest parameters for their data source 

**ANDS DOI Service**
* Fix bug preventing updates to client accounts where the client id is a single digit

**RIF-CS (v1.6.2)**
* Addition of IGSN, GRID, ScopusID, ISNI and RAiD as identifier types in RIF-CS
* Addition of PONT, SCOT, APT, PSYCHIT and ISO639 subject types in RIF-CS
* Simplification of RIF-CS identifier types

**Research Vocabularies Australia**
 * Include missing JAR files in the vocabulary toolkit to allow parsing of vocabularies in some RDF formats (N3, N-Quads and TriX)

**RDA-ORCID Integration Service**
* Retrieve and index ORCID name if ORCID ID is used as relatedInfo identifier
* ORCID Works Export: Upgrade the ORCID Works Export service to use ORCID API v2.0 
* RDA to ORCID Import: Revisit business rules for extracting creation date when citationMetadata is not available
* RDA to ORCID Import: Add better error reporting
* ORCID Import wizard: change default type to 'dataset' (currently set to 'other')

Please note that changes listed above are the planned features, subject to  timetabling  changes.

A separate email with the list of impact and requirements for each change will be sent out before the release. 

For any comments or questions, please email [services@ardc.edu.au](mailto:services@ardc.edu.au). Thank you.


## Release 25
Release 25 of ANDS Online Services is scheduled for implementation on  11 October 2017. This release implements a number of Research Data Australia, Research Vocabularies Australia and ANDS Registry bug fixes and enhancement requests received through the ANDS Service Desk.

*Research Data Australia*

* Correct the display title for party records with both 'superior' and 'subordinate' primary name parts
* Update the Temporal search filter behaviour to be inclusive
* Remove unnecessary javascript causing unexpected scroll behaviour
* Fix issue where access points for collections are being duplicated under the Go To Data Provider button on the view page
* Ensure RDA URLs with only the ID are resolved to the full URL

*Research Vocabularies Australia*

* Correctly display related entity URLs and identifiers in the related entity preview popout on the vocabulary view page
* Clean-up of existing Sesame repository on re-import of a vocabulary version

*ANDS Registry*

* Display a user-friendly version of the development changelog on the Online Services Homepage

## Release 24
Release 24 of ANDS Online Services is scheduled for implementation on **5 July 2017 from 8:00am to 12pm**. During the implementation, the following services will be unavailable:

*   Research Data Australia (RDA)
*   RDA Registry
*   ANDS DOI Service
*   Research Vocabularies Australia (RVA)
*   ANDS Handle Service

*Research Data Australia (RDA)*
*   Fix the issue with the display of some special characters in RDA
*   Update subject resolution in RDA such that only language codes with correct correct types (ISO 639) are resolved to their string names
*   Implement a fix to prevent duplicate records from displaying as additional related objects in an Activity view page
*   ARC and NHMRC grants refresh

*RDA Registry*
*   Fix the issue with the Harvester dying unexpectedly preventing completion of scheduled harvests 
*   Fix a validation error bug when editing a record with more than 30 relatedObjects/relatedInfo

*DOI Service*
*   Implement a fix to the the DOI manual minting service to ensure updates are processed only when both URL and XML pass validation
*   Reactivate deactivated DOI on post of new metadata to keep in sync with DataCite
*   Enhance CMD DOI request events to capture more transaction information 

*Handle Service (PIDS)*
*   As part a recent infrastructure migration project we have implemented a new service URL for the Handle Service ([https://handle.ands.org.au/pids](https://handle.ands.org.au/pids)) . While we will continue to support the previous URL but we encourage users of the service to migrate to the new URL when convenient. Please refer to the [Handle Service documentation](https://documentation.ardc.edu.au/display/DOC/Basic+Service+Information) for more information.

*Research Vocabularies Australia (RVA)*
*   Fix the display of the organisations in the RVA CMS' 'Owner' field to also include the complete name of the organisation (e.g from 'ANDS' to 'Australian National Data Service (ANDS)) 
*   Improve the guidance text on the 'Provide Feedback' page in the RVA to provide clearer instructions 
*   Reword the tooltip/help text for the 'Top Concepts' section in the vocabulary metadata CMS 
*   Optimise Resource Mapping process to prevent timeouts when publishing large vocabularies 

*Other (Widget, Tools, APIs, etc.)*
*   ORCID Wizard: Update the API to prevent relatedInfo/identifier from being picked up as a collection(works) identifier 

Please also note: 
*   As part of Release 24 ANDS will be migrating its [services.ands.org.au](http://services.ands.org.au/) domain to a new host. This however should not impact any of our service consumers. If you have any concerns please get in contact before the 5th July. 

A reminder email will be sent out on implementation day.

Please schedule your activities accordingly. For any comments or questions, please email [services@ardc.edu.au](mailto:services@ardc.edu.au). Thank you.
