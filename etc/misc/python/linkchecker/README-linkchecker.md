ANDS Registry link checker
==========================

This directory contains the ANDS Registry link checker, a Python
package to check the links associated with:

* DOIs (digital object identifiers)
* Registry objects

This link checker is intended to serve the following functions:

* as a drop-in replacement for the existing DOI link checker script
  `doi_link_checker.py`;
* as support for checking links within registry objects; and
* as a framework for link checking of additional types of links.

The link checker has been developed using Python 3.4. In particular,
it uses the `asyncio` library that was first made available in
version 3.4 of Python.


## Release versions

* ANDS Registry Release 14: initial release of the link checker.


## Modules currently supported

These are the currently supported link checking modules:

* `DOI`: The link checker for DOIs
* `RO`: The link checker for registry objects

As explained below, the link checker uses module names as:

* section names in configuration files; and
* values specified with the `-m` command-line option.

The behaviour of the link checker modules is determined largely by the
needs of ANDS. If you need other behaviour, please contact
`services@ands.org.au` with your requirements.


### The DOI link checker

As mentioned above, this checker is intended as a replacement for the
existing DOI link checker. As such, a certain level of compatibility
has been provided. For example, the existing DOI link checker always
outputs HTML on the console. This new DOI link checker module normally
outputs plain text, but can be configured to output HTML using a
command-line option (`--html_output`). When incorporating the link
checker into the ANDS Registry administration web interface, this
option should be used to maintain compatibility.  See the invocation
section below for specific examples.


### The RO link checker

The registry objects link checker includes detailed reports in its
generated emails as an attached CSV file. This file is suitable for
opening in common spreadsheet programs. It is important to set the
`registry_prefix` option correctly in the configuration file (see
below) so that the generated links to the corresponding registry
objects are correct.

The registry objects link checker works by querying the
`registry_object_links` table that has been added in Release 14 of the
Registry software. Hence, it can not be used with previous releases of
the Registry.


## Using the link checker

A particular invocation of the link checker is configured using a
combination of a configuration file and command-line options.


### Link checker configuration file

A configuration file in "Windows INI" format must be provided when
invoking the link checker.  Default settings that apply to all types
of checker can be provided in a section labelled `DEFAULT`. Settings
that apply to a particular checker are specified in a section named
after the checker module (i.e., `DOI`, or `RO`).

The file `linkchecker.ini.template` contains a template suitable for
constructing your own `linkchecker.ini` file.  The file
`linkchecker.ini.sample` is a sample instantiation of this template.


### Command-line options

Invoking the link checker with the `-h` command-line option will cause
it to display a summary of the available options.

These options are always required:

* `-i ini_file`: Specify the configuration file to be read
* `-m module`: Specify the checker module to be used

These options are optional:

* `-c client_id`: Specify a particular "client" for checking. The
  meaning of "client" depends on the module being used for checking.
  For the `DOI` module, this is a DOI client ID. For the `RO` module,
  this is a data source ID.
* `-e admin_email`: Specify an overriding email address to use as
  the recipient of all outgoing emails. Without the `-e` option, the
  checker determines the email recipient(s) automatically.
* `--html_output`: The console output of the script is normally plain
  text. Specifying this option causes `<br />` tags to be included
  after each line, making the output suitable for inclusion in content
  displayed as part of a web page.
* `--no_emails`: If this option is specified, the checker will not
  send out any emails.
* `-d`: If this option is specified, the checker will print a
  considerable amount of debugging information on standard error.


## Invoking the link checker

`python3.4 linkchecker.py options...`

For example:

* `python3.4 linkchecker.py -i linkchecker.ini -m DOI`: Check all
  DOIs. Emails are sent to the emails registered for each client.
* `python3.4 linkchecker.py -i linkchecker.ini -m DOI -c 35
  --html_output`: Check all DOIs for client ID 35. An email will be sent
  to the address registered for this client ID. Console output will
  include `<br />` tags so that it can be included as part of a web
  page.
* `python3.4 linkchecker.py -i linkchecker.ini -m RO 47`: Check links in
  all registry objects belonging to data source 47. An email will be
  sent to the address registered for this data source.

The following sections show how the link checker can be integrated
into a production system.


### DOI link checker called from the Registry


The setting of the `DOI_LINK_CHECKER_SCRIPT` in `global_config.php`
should be set to something like this:

     $ENV['DOI_LINK_CHECKER_SCRIPT'] = "/full/path/to/linkchecker.py --html_output -i /full/path/to/linkchecker.ini -m DOI";


### DOI link checker called from cron

A crontab entry to send reports to all DOI clients quarterly might
look like this:

    30 03 01 */3 * /full/path/to/python3.4 /full/path/to/linkchecker.py -i /full/path/to/linkchecker.ini -m DOI >/dev/null 2>&1


### RO link checker called from cron

A crontab entry to send reports to all data source contacts quarterly
might look like this:

    30 03 05 */3 * /full/path/to/python3.4 /full/path/to/linkchecker.py -i /full/path/to/linkchecker.ini -m RO >/dev/null 2>&1

Or, to send all reports to a central administrator::

    30 03 05 */3 * /full/path/to/python3.4 /full/path/to/linkchecker.py -i /full/path/to/linkchecker.ini -m RO -e admin.email@admin.com >/dev/null 2>&1
