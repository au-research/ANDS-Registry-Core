"""
Tests of the parsing of INI-style configuration data.

(Only one test so far.)
"""

import configparser

# A list of required options for each section.
ALL_REQUIRED_OPTIONS = {
    'sender_email',
    'smtp_host',
    'database_module',
    'database_host',
    'database_user',
    'database_password',
    'ssl_certificate',
    'database_name',
}

# Simple test with multiple sections. One option is missing from RO.
test_parse_ini1_conf = """\
# Test configuration for link checker.
# If you modify this file, you must also update the tests!

# The test code tests for:
# * The presence of DOI and RO sections
# * The RO section to be missing a setting for 'database_name'.

# Top-level configuration. Must be in a section "DEFAULT".
[DEFAULT]

# Email address to use as the sender of generated emails.
sender_email      = Richard.Walker@ands.org.au

# SMTP host
smtp_host         = localhost

# Database connection for accessing the links to be checked.
database_module   = pymysql
database_host     = testhost
database_user     = testuser
database_password = testpassword

# Location of an SSL certificate to use when checking a link over HTTPS.
# In general, this should not be anything special.  However, some
# HTTP servers reject the connection if the provided certificate
# is not signed. It is sufficient to provide a new, self-signed
# certificate.
ssl_certificate   = cert.pem


# Override settings for checking DOI links
[DOI]
database_name     = dbs_dois

# Override settings for checking Registry objects
[RO]
#database_name    = dbs_registry
"""


def test_parse_ini1():
    """Simple test requiring sections for DOI and RO, and that the RO
    section be missing a setting for database_name.
    """
    config = configparser.ConfigParser()
    config.read_string(test_parse_ini1_conf)
    assert set(config.sections()) == {'DOI', 'RO'}
    for s in config.sections():
        print('Found section: [', s, ']', sep='')
        for c in config.options(s):
            print('Found option:', c, '=', config[s][c])
        options_missing = ALL_REQUIRED_OPTIONS - set(config[s])
        if options_missing:
            print('Required options missing:')
            for missing in options_missing:
                print(missing)
                assert s == 'RO' and missing == 'database_name'

if __name__ == '__main__':
    test_parse_ini1()
