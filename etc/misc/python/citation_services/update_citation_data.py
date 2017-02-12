"""
ANDS citation services data fetcher.

Fetch citation information from citation data providers.

Currently, Thomson Reuters is supported.
"""

"""
Control flow is:

main()
  process_args()
    process_ini_file()
  open_db_connection()
  do_citation_data_updating()
"""

# Version number. Printed out as part of HELP_TEXT.
VERSION = "0.0.1"

# System libraries
import configparser
import getopt
import re
import sys

# Third-party libraries
import pymysql

# Import all services
# import services
import services.TRDCI

# Help text printed with the -h option, or if an error occurs during
# option processing.
HELP_TEXT = """\
ANDS citation data updater version {}.
update_citation_data.py -h
  Display this help text.
update_citation_data.py [option]...
  Where options are:
  -d
    Turn on some debugging
  [-i | --ini=] ini_file
    Specify a configuration file to be loaded, in INI format
  [-m | --module=] module
    Specify the module to use for citation data, e.g., TRDCI
  [-c | --client_id=] client_id
    Specify the client ID of registry objects to be looked up.
    For the TRDCI service, this is a data source ID.
  [-e | --admin_email=] admin_email
    Specify the email address of the recipient of the resulting email(s)
  [ --html_output ]
    The citation module will output its result summary in HTML format rather
    than plain text.
  [ --no_emails ]
    Citation data is checked, but no emails will be sent.
    This enables a type of "dry run".
""".format(VERSION)


def process_args(argv):
    """Process command-line arguments.

    This will:
    * Determine which citation service to use.
    * Determine database connection details.
    The results of processing are returned in a dictionary with some or
    all of the following keys:
    * debug: True, if debugging output is requested
    * ini_file: The file name of the specified INI file
    * config: The configparser object based on parsing the INI file
    * module: The name of the requested citation service module (TRDCI, etc.)
    * client_id: The number of the client ID
    * admin_email: The email address to use for sending reports
    Additionally, options are extracted from the INI file for the
    specified module. For example, if the command-line contains
    "-i inifile -m DOI", then all the options from the DOI section
    of inifile are copied into the dictionary as key/value pairs

    Arguments:
    argv -- The command-line arguments.
    Return value:
    The parameters to use for looking up citation data.
    """
    # Create dictionary in which to store the parameters for the
    # citation service.
    # Some defaults are set here.
    params = {'client_id': None,
              'admin_email': None}
    # Parse the command-line options.
    try:
        opts, args = getopt.getopt(
            argv[1:], 'hdi:m:c:e:',
            ['ini=', 'module=', 'client_id=', 'admin_email=',
             'html_output', 'no_emails'])
    except getopt.GetoptError:
        print(HELP_TEXT)
        sys.exit(2)
    # Now process them.
    for opt, arg in opts:
        if opt == '-h':
            print(HELP_TEXT)
            sys.exit(0)
        elif opt in ('-d'):
            params['debug'] = True
        elif opt in ('-i', '--ini'):
            params['ini_file'] = arg
            process_ini_file(params, arg)
        elif opt in ('-m', '--module'):
            if not re.match(r'[A-Za-z0-9_]+$', arg):
                # For security reasons, require alphanumeric and underscores
                # only.  This prevents sneaky stuff when finding the Python
                # module, e.g., using "../" and the like.
                print('Module name must be alphanumeric:', arg)
                sys.exit(5)
            params['module'] = arg
        elif opt in ('-c', '--client_id'):
            params['client_id'] = int(arg)
        elif opt in ('-e', '--admin_email'):
            params['admin_email'] = arg
        elif opt == '--html_output':
            params['html_output'] = True
        elif opt == '--no_emails':
            params['no_emails'] = True
    # For now, require that an ini file was provided.
    if 'ini_file' not in params:
        print('You must specify a configuration file with the -i option.')
        sys.exit(4)
    # Require a module to have been specified.
    if 'module' not in params:
        print('No citation service module was selected. '
              'Select a module with -m.')
        sys.exit(4)
    # If both '-i' and '-m' were specified, copy the corresponding config
    # elements into params
    if 'config' in params and 'module' in params:
        if not params['config'].has_section(params['module']):
            print('Configuration file read, but has no section for module:',
                  params['module'])
            sys.exit(4)
        for c in params['config'].options(params['module']):
            params[c] = params['config'][params['module']][c]
    return params


# A list of required options for each section in an INI file.
ALL_REQUIRED_OPTIONS = {
    'sender_email',
    'smtp_host',
    'database_module',
    'database_host',
    'database_user',
    'database_password',
    'database_name',
}


def process_ini_file(params, ini_file_filename):
    """Process a configuration file in INI format.

    Arguments:
    params -- The current dictionary of parameters as processed so far;
      updated in place with the contents of the INI file.
    ini_file_filename -- The filename of the INI file.
    """
    config = configparser.ConfigParser()
    config.read(ini_file_filename)
    for s in config.sections():
        options_missing = ALL_REQUIRED_OPTIONS - set(config[s])
        if options_missing:
            print('Required options missing from section ',
                  s, ':', sep='')
            for missing in options_missing:
                print(missing)
            sys.exit(3)
    params['config'] = config


def open_db_connection(params):
    """Establish a connection with the database.

    Only pymysql is supported as the database module.

    Future work for this function:
    * Support other database modules (PostgreSQL, etc.)
    * When we do that, only load the one database Python module required,
      not all.

    Arguments:
    params -- The dictionary of parameters, which must include
      all those needed to establish the connection.
    """
    if params['database_module'] != 'pymysql':
        print("Sorry, only pymysql is supported as the database module.")
        sys.exit(5)
    try:
        return pymysql.connect(
            host=params['database_host'],
            user=params['database_user'],
            passwd=params['database_password'],
            db=params['database_name'])
    except Exception as e:
        print("Database Exception:", e)
        sys.exit(1)


def do_citation_data_updating(conn, params):
    """Do the updating of citation data.

    Arguments:
    conn -- The database connection.
    params -- The citation service parameters.
    """
    # Delegate to the module-specific citation service
    # TODO: Use the module name directly as a class name.
    if params['module'] == 'TRDCI':
        service = services.TRDCI.TRDCIService(conn, params)
    else:
        print('Unknown service module: ', params['module'])
        sys.exit(6)
    service.do_update_citation_data()


def main(argv):
    """Main function of the citation data updater.

    Process command-line arguments.
    Do citation data updating and report/email the results.

    Arguments:
    argv -- The command-line arguments.
    """
    params = process_args(argv)
    if 'debug' in params:
        print('DEBUG: params =', params, file=sys.stderr)
    # Would have liked to have used a "with" statement here, but
    # unfortunately that (with the pymysql driver, at least) creates a
    # cursor with commit/rollback behaviour on exit.  We want more
    # fine-grained control.
    database_connection = open_db_connection(params)
    if 'debug' in params:
        print('DEBUG: connected to database', file=sys.stderr)
    try:
        do_citation_data_updating(database_connection, params)
    finally:
        database_connection.close()

# Execution of this module begins here.
if __name__ == "__main__":
    main(sys.argv)
    # try:
    #     main(sys.argv)
    # except Exception as e:
    #     print("Terminating with exception:", e)
    #     sys.exit(1)
