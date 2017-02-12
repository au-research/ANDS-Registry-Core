ANDS Registry citation data updater
===================================

This directory contains the ANDS Registry citation data updater, a Python
package to fetch citation data from service providers, currently:

* Thomson Reuters Data Citation Index

This updater is intended to serve the following functions:

* as support for fetching citation data for registry objects; and
* as a framework for fetching citation data from additional providers.

The updater has been developed using Python 3.4.


## Release versions

* ANDS Registry Release 15: initial release of the citation data updater.


## Important note about the Git repository

There is a .gitignore file in this directory, which excludes
update_citation_data.ini. If you clone this repository, but you want
to store and commit changes to your update_citation_data.ini in the
clone, remove this .gitignore file.


## Modules currently supported

These are the currently supported citation data modules:

* `TRDCI`: The Thomson Reuters Data Citation Index

As explained below, the citation data updater uses module names as:

* section names in configuration files; and
* values specified with the `-m` command-line option.

The behaviour of the updater modules is determined largely by the
needs of ANDS. If you need other behaviour, please contact
`services@ands.org.au` with your requirements.


### The TRDCI citation data updater

The TRDCI citation data updater works by querying the
`registry_object_citations` table that has been added in Release 15 of
the Registry software. Hence, it can not be used with previous
releases of the Registry.


## Using the citation data updater

A particular invocation of the updater is configured using a
combination of a configuration file and command-line options.


### Updater configuration file

A configuration file in "Windows INI" format must be provided when
invoking the updater.  Default settings that apply to all types
of updater can be provided in a section labelled `DEFAULT`. Settings
that apply to a particular updater are specified in a section named
after the updater module (i.e., `TRDCI`).

The file `update_citation_data.ini.template` contains a template
suitable for constructing your own `update_citation_data.ini` file.
The file `update_citation_data.ini.sample` is a sample instantiation
of this template.


### Command-line options

Invoking the updater with the `-h` command-line option will cause
it to display a summary of the available options.

These options are always required:

* `-i ini_file`: Specify the configuration file to be read
* `-m module`: Specify the service module to be used

These options are optional:

* `-c client_id`: Specify a particular "client" for checking. The
  meaning of "client" depends on the module being used for checking.
  For the `TRDCI` module, this is a data source ID.
* `-e admin_email`: This option is not currently used. Specify an
  overriding email address to use as the recipient of all outgoing
  emails. Without the `-e` option, the updater determines the email
  recipient(s) automatically.
* `--html_output`: This option is not currently used. The console
  output of the script is normally plain text. Specifying this option
  causes `<br />` tags to be included after each line, making the
  output suitable for inclusion in content displayed as part of a web
  page.
* `--no_emails`: This option is not currently used. If this option is
  specified, the updater will not send out any emails.
* `-d`: If this option is specified, the updater will print a
  considerable amount of debugging information on standard error.


## Invoking the updater

`python3.4 update_citation_data.py options...`

For example:

* `python3.4 update_citation_data.py -i update_citation_data.ini -m
  TRDCI`: Look up citation data for all registry objects present in
  the `registry_object_citations` table.
* `python3.4 update_citation_data.py -i update_citation_data.ini -m TRDCI -c 35 --html_output`:
  Look up TR DCI citation data for registry objects belonging
  to data source ID 35. Any console output will
  include `<br />` tags so that it can be included as part of a web
  page.
* `python3.4 update_citation_data.py -i update_citation_data.ini -m TRDCI -c 47`:
  Look up Thomson Reuters citation data in all registry objects
  belonging to data source 47.

The following sections show how the citation data updater can be
integrated into a production system.


### TRDCI updater called from cron

A crontab entry to update citation data for all registry objects might
look like this:

    30 03 05 */3 * /full/path/to/python3.4 /full/path/to/update_citation_data.py -i /full/path/to/update_citation_data.ini -m TRDCI >/dev/null 2>&1

Or, to send all reports to a central administrator:

    30 03 05 */3 * /full/path/to/python3.4 /full/path/to/update_citation_data.py -i /full/path/to/update_citation_data.ini -m TRDCI -e admin.email@admin.com >/dev/null 2>&1

Note: as mentioned above, currently, no emails are sent.
This may be added in a future release.

## Populating the `registry_object_citations` table

The updater only updates existing records in the
`registry_object_citations` table; it does not currently add new rows.
(However, it does add new rows to the `record_stats` table of the
portal database.)

To populate the `registry_object_citations` table with suitable
entries to be looked up in the citation service, you might execute
queries like the following, in which XXX, YYY, ZZZ must be replaced
with the data source IDs of appropriate data sources.

    insert into registry_object_citations
    (registry_object_id,data_source_id,service_provider,query_terms,citation_data)
    select distinct
      ro.registry_object_id,
      ro.data_source_id,
      'TRDCI',
      concat('{"doi":"',roi.identifier,'"}'),
      ''
    from registry_object_identifiers roi,registry_objects ro
      where ro.data_source_id in (XXX, YYY, ZZZ)
      and ro.registry_object_id = roi.registry_object_id
      and roi.identifier_type='doi' 
      and ro.class='collection'
      and ro.status='PUBLISHED'
      ;

    insert into registry_object_citations
    (registry_object_id,data_source_id,service_provider,query_terms,citation_data)
    select distinct
      ro.registry_object_id,
      ro.data_source_id,
      'TRDCI',
      concat('{"doi":"',roi.identifier,'","atitle":"',
      replace(replace(ro.title,'\\','\\\\'),'"','\\"'),
      '"}'),
      ''
    from registry_object_identifiers roi,registry_objects ro
      where ro.data_source_id in (XXX, YYY, ZZZ)
      and ro.registry_object_id = roi.registry_object_id
      and roi.identifier_type='doi' 
      and ro.class='collection'
      and ro.status='PUBLISHED'
      ;
