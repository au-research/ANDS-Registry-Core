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
*   As part a recent infrastructure migration project we have implemented a new service URL for the Handle Service ([https://handle.ands.org.au/pids](https://handle.ands.org.au/pids)) . While we will continue to support the previous URL but we encourage users of the service to migrate to the new URL when convenient. Please refer to the [Handle Service documentation](https://documentation.ands.org.au/display/DOC/Basic+Service+Information) for more information.

*Research Vocabularies Australia (RVA)*
*   Fix the display of the organisations in the RVA CMS' 'Owner' field to also include the complete name of the organisation (e.g from 'ANDS' to 'Australian National Data Service (ANDS)) 
*   Improve the guidance text on the 'Provide Feedback' page in the RVA to provide clearer instructions 
*   Reword the tooltip/help text for the 'Top Concepts' section in the vocabulary metadata CMS 
*   Optimise Resource Mapping process to prevent timeouts when publishing large vocabularies 

*Other (Widget, Tools, APIs, etc.)*
*   ORCID Wizard: Update the API to prevent relatedInfo/identifier from being picked up as a collection(works) identifier 

Please also note: 
*   As part of Release 24 ANDS will be migrating it's [services.ands.org.au](http://services.ands.org.au/) domain to a new host. This however should not impact any of our service consumers. If you have any concerns please get in contact before the 5th July. 

A reminder email will be sent out on implementation day.

Please schedule your activities accordingly. For any comments or questions, please email [services@ands.org.au](mailto:services@ands.org.au). Thank you.