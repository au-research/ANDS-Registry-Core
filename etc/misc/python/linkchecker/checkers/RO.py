"""
Link checker for registry object links in the ANDS Registry.
"""

"""
The registry object link checking process relies on a table
"registry_object_links".

The link checker operates as follows:
1. Get the links to check from the database.
2. Check the links, updating each one's status in the database.
3. Query the database again to get the links and their status.
4. Send emails.


Data structures used throughout this module:

data_sources: dict
  key: int: data_source_id
  value: dict: Details about this data_source.
    key: "title"
    value: The value of the "title" column of the data source.
    key: "email"
    value: The value of the "contact_email" attribute of the data source,
      if there is one. Note: in practice, this may be blank, or have spaces.
    Other keys/values to be added?
    key: str: TBD
    value: str: TBD

(rol = "registry object link")
rol_list: list
  element: dict: a row from the registry_object_links table.
    key: one of the column names in the registry_object_links table.
    value: the value of that column

testing_array: dict (despite the name, grr)
  key: int: An index into the rol_list array.
  value: dict: Details of the link to be tested.
    There are three key/value pairs in each dictionary,
    taken from the corresponding tuple in rol_list.
    key: "url_str"
    value: The value of the "url" column (whitespace stripped).
    key: "creator"
    value: The value of the "client_id" column.
    key: "doi_id"
    value: The value of the "doi_id" column (whitespace stripped).

test_results: dict
  key: str: a link that has been tested
  value: str: the result of testing

result_list: dict
  key: int: data_source_id.
  value: str: text containing a list of the broken links
    for this data source (for insertion into an email going to the owner
    of the data source).

error_count: dict
  key: int: data_source_id
  value: int: The number of errors encounted when checking the links
    belonging to the data source.
"""

import asyncio
import asyncio.futures
import datetime
import socket
import sys
import time
import urllib

# The base module contains the BaseChecker class.
from . import base


class ROChecker(base.BaseChecker):
    """Checker for registry objects.
    """

    # registry_prefix: "http://path-to-registry-objects/"
    #   A trailing slash will be added, if there isn't one.
    required_options = {'registry_prefix'}

    def do_link_checking(self):
        """Do the link checking.
        """
        # Add a trailing slash to registry_prefix, if there isn't
        # one already.
        if not self._params['registry_prefix'].endswith('/'):
            self._params['registry_prefix'] += '/'

        data_sources = {}
        self._get_data_sources(data_sources, self._params['client_id'])

        test_results = {}
        error_count = {}

        self._run_tests(self._params['ssl_context'],
                        self._params['client_id'],
                        self._params['admin_email'], data_sources,
                        int(self._params['link_timeout']),
                        int(self._params['batch_size']),
                        test_results, error_count)
        if self._debug:
            print("DEBUG: test_results:", test_results, file=sys.stderr)

        self._process_results(data_sources, test_results,
                              self._params['client_id'],
                              self._params['admin_email'])

    # All the columns queried from the data_sources table, in order.
    DATA_SOURCES_COLUMNS = """\
      `data_source_id`,
      `title`
    """

    def _get_data_sources(self, data_sources, data_source_id):
        """Get data source information for ROs.

        Get data source information for generating a personalised record
        for each test run.
        Arguments:
        data_sources -- The dictionary to be populated with the results
            of the database query.
        data_source_id -- A data_source_id to use for searching the database,
            or None, if all data sources are to be returned.
        """
        cur = self._conn.cursor()
        query = "SELECT " + self.DATA_SOURCES_COLUMNS + " FROM data_sources"
        if data_source_id is not None:
            cur.execute(query + " where `data_source_id`=" +
                        str(data_source_id) + ";")
        else:
            cur.execute(query + ";")
        for r in cur:
            data_sources[r[0]] = {
                'title': r[1]
                }
            if self._debug:
                print("DEBUG: Assigned data_sources[{}] = {}".format(
                    r[0], data_sources[r[0]]), file=sys.stderr)
        if not self._params['admin_email']:
            # No admin_email provided, so the checker will send
            # emails to data source administrators. Get them
            # from the data_source_attributes table.
            for k, v in data_sources.items():
                query = ("SELECT value FROM data_source_attributes " +
                         "WHERE data_source_id = " + str(k) +
                         " AND attribute = 'contact_email';")
                if self._debug:
                    print("DEBUG: query: ", query, file=sys.stderr)
                cur.execute(query)
                row = cur.fetchone()
                if row:
                    v['email'] = row[0]
                else:
                    # No email defined. What to do?
                    # NB, some data sources have an
                    # "assessment_notify_email_addr" attribute; maybe
                    # use that.
                    pass
                if self._debug:
                    print("DEBUG: Assigned data_sources[{}] = {}".format(
                        k, data_sources[k]), file=sys.stderr)
        cur.close()

    def _run_tests(self, ssl_context, client_id, admin_email, data_sources,
                   link_timeout, batch_size,
                   test_results, error_count):
        """
        Arguments:
        ssl_context -- The SSL context to use when making HTTP requests.
        client_id -- A client_id to use for searching the database,
        admin_email -- If not None, the email address to use as
            recipient of all outgoing messages.
        data_sources -- The details of the data sources being checked.
        link_timeout -- Timeout to use, in seconds.
        batch_size -- Maximum number of concurrent link checks.
        test_results -- The dict containing the details of test results.
        error_count -- The errors resulting from the tests.
        """

        rol_list = []
        testing_array = {}

        self._get_RO_links_for_checking(rol_list, client_id)

        timestamp = datetime.datetime.now().strftime("%Y-%m-%d %H:%M")
        if self._debug:
            print("DEBUG: Using timestamp:", timestamp, file=sys.stderr)

        REPORT_HEADER = "Number of URLs to be tested: " + str(len(rol_list))
        self.print_text_or_html(REPORT_HEADER,
                                REPORT_HEADER + "\n<br />")
        socket.setdefaulttimeout(link_timeout)
        loop = asyncio.get_event_loop()
        # Sleep 1 before getting started. (Why?)
        time.sleep(1)
        # The variable "batch_number" iterates over batches
        # of size batch_size.
        batch_number = 0
        # Store the length of rol_list for convenience
        len_rol_list = len(rol_list)
        while len_rol_list > (batch_number * batch_size):
            task_array = []
            # This range() iterates over a range of size (at most) batch_size
            for i in range(batch_number * batch_size,
                           min((batch_number + 1) * batch_size,
                               len_rol_list)):
                if self._debug:
                    print("DEBUG: i =", i, "; rol_list[i] =", rol_list[i],
                          file=sys.stderr)
                testing_array[i] = {"url_str": rol_list[i]['link'].strip()}
                task_array.append(asyncio.async(
                    self._check_URL_resource(ssl_context,
                                             timestamp,
                                             rol_list[i],
                                             i,
                                             testing_array,
                                             test_results)))
            try:
                loop.run_until_complete(asyncio.wait(task_array,
                                                     timeout=link_timeout))
                # If a test is successful, the corresponding entry in
                # testing_array is deleted.
                # So when run_until_complete returns, the entries
                # remaining in testing_array are all timeouts.
                for k, v in testing_array.items():
                    self._mark_status_and_timestamp(v['url_str'],
                                                    "BROKEN",
                                                    timestamp)
                    test_results[v['url_str']] = "CONNECTION TIMEOUT"
                testing_array.clear()
            except ValueError:
                print("i: {}, range start {}, end {}".
                      format(i,
                             batch_number * batch_size,
                             ((batch_number + 1) * batch_size)))
            finally:
                # Clean up all pending tasks. See:
                # https://groups.google.com/d/msg/python-tulip/
                #         qQbdxREjn1Q/guWqL8tjH8gJ
                for t in asyncio.Task.all_tasks(loop):
                    # print("Cancelling task: ", t)
                    t.cancel()
                # Give cancelled tasks a chance to recover.
                loop.run_until_complete(asyncio.sleep(0.1))
            batch_number += 1
        loop.close()

    # Columns in the registry_object_links table.
    # RO_LINKS_COLUMNS = [
    #     'id',
    #     'registry_object_id',
    #     'data_source_id',
    #     'link_type',
    #     'link',
    #     'status',
    #     'last_checked']
    # Columns needed to get the links for querying.
    RO_LINKS_CHECKING_COLUMNS = [
        'link_type',
        'link']

    def _get_RO_links_for_checking(self, rol_list, data_source_id=None):
        """Get all RO links to be tested.

        The rol_list array is updated in situ.

        Arguments:
        rol_list -- The array to be populated with RO link data
            from the database.
        data_source_id -- A data_source_id to use for searching the database,
            or None, if the ROs of all data sources are to be returned.
        """
        cur = self._conn.cursor()

        # Put backquotes around the column names.
        columns_for_query = ["`" + c + "`"
                             for c in self.RO_LINKS_CHECKING_COLUMNS]
        # Use "DISTINCT", as the same link may appear multiple
        # times, but we only need to check it once.
        # (Cf. _get_RO_links_for_reporting().)
        query = ("SELECT DISTINCT " + ", ".join(columns_for_query) +
                 " FROM registry_object_links")
        if data_source_id is not None:
            query += " WHERE `data_source_id`=" + str(data_source_id)
        query += ";"
        if self._debug:
            print("DEBUG: get_RO_links query:", query, file=sys.stderr)
        cur.execute(query)
        for r in cur:
            # Turn the result tuple into a dict with the column names as keys
            rol = {k: v for k, v in zip(self.RO_LINKS_CHECKING_COLUMNS, r)}
            # If url is missing (NULL in the database), set it to
            # an empty string. This allows calling strip() on it
            # later (in _run_tests).
            if not rol['link']:
                rol['link'] = ""
            # Only add links that are of the right type.
            if self._is_link_to_be_checked(rol):
                rol_list.append(rol)
        cur.close()
        if self._debug:
            print("DEBUG: _get_RO_links_for_checking rol_list:",
                  rol_list, file=sys.stderr)

    def _is_link_to_be_checked(self, rol):
        """Determine if this link is to be checked.

        A link is to be tested if either:
        * The link_type ends with "_url"
        * The link_type is "description_link"
        * The link begins with "http"

        Arguments:
        rol -- A registry object link dict, with (at least)
            "link_type" and "link" keys.
        Return value:
        True, iff this link is one that this checker will test.
        """
        # return re.match(r'.*_url$', link_type)
        link_type = rol['link_type']
        return (link_type.endswith('_url') or
                link_type == 'description_link' or
                rol['link'].startswith('http'))
        pass

    # Format string for HEAD query.
    # NB: The Keep-Alive entry is for possible future work:
    #     doing a subsequent GET request to analyse the page content.
    # Replacement fields:
    # url_path -- The query URL to be sent.
    # url -- The entire URL object, as returned by urlsplit().
    HEAD_QUERY_FORMAT = (
        'HEAD {url_path} HTTP/1.0\r\n'
        'Host: {url.hostname}\r\n'
        'User-agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; '
        'en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6\r\n'
        'Accept: text/xml,application/xml,application/xhtml+xml,'
        'text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5\r\n'
        'Accept-Language: en-us,en;q=0.5\r\n'
        'Accept-Encoding: gzip,deflate\r\n'
        'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n'
        'Keep-Alive: 300\r\n'
        '\r\n')

    # Maximum number of attempts to check a link.
    # This includes testing the original URL, and following redirects.
    # Why 7? That is the number of attempts (including the source
    # URL) made by the original DOI link checker.
    # This constant might be suitable for turning
    # into a configuration parameter.
    ATTEMPTS_MAX = 7

    @asyncio.coroutine
    def _check_URL_resource(self, ssl_context,
                            timestamp,
                            r, counter,
                            testing_array,
                            test_results):
        """Check one URL resource.

        Request the header for each resource and try to determine
        if it is resolvable.
        Record a log entry if an exception occurs, or the server
        returns a 400/500 error.

        Arguments:
        ssl_context -- The SSL context to use when making HTTP requests.
        timestamp -- The timestamp to use when storing the test result.
        r -- The tuple containing a row from the doi_object table with
            the details of the link to be tested.
        counter -- The key of testing_array corresponding to this test.
            If the key is valid, and the link is valid, the key/value pair
            will be removed from testing_array.
        testing_array -- The dict containing the details of the current batch
            of tests.
        test_results -- The dict containing the details of test results.
        """
        # Hmm, why did we put the data in testing_array?
        # See _run_tests for the same code.
        url_str = url_str_original = r['link']

        SCHEME_NOT_HTTP_FORMAT = ('Error: Scheme is not http(s): ')
        URL_PARSE_ERROR_FORMAT = ('Error: Parsing URL failed')
        STATUS_ERROR_FORMAT = '4/500s: Status {}'
        NO_STATUS_ERROR_FORMAT = ('Error: Server did not return an '
                                  'HTTP status code')
        REDIRECT_SAME_FORMAT = ('Error: Redirect URL same as original: ')
        EXCEPTION_FORMAT = 'Error: {}'
        TOO_MANY_REDIRECTS_FORMAT = ('Error: too many redirects: '
                                     'FINAL URL: {}')
        SUCCESS = 'SUCCESS'
        try:
            # First time round (i.e., before attempting to follow any
            # redirects), do a small sleep. This helps avoid
            # DoS attacking the server.
            # NB This "should" say "yield from asyncio.sleep(0.3)",
            # but we do really want the whole system to pause at
            # this point, to give a delay between each
            # connection initiation.
            time.sleep(0.3)
            for redirect_count in range(0, self.ATTEMPTS_MAX):
                url = urllib.parse.urlsplit(url_str)
                if not url.scheme.startswith('http'):
                    # The scheme must begin with "http",
                    # i.e., be either "http" or "https".
                    self._handle_one_error(url_str_original,
                                           SCHEME_NOT_HTTP_FORMAT,
                                           timestamp,
                                           testing_array,
                                           counter,
                                           test_results)
                    return
                if not url.hostname:
                    # Something wrong with the parsing of the URL,
                    # possibly "http:/only-one-slash.com".
                    self._handle_one_error(url_str_original,
                                           URL_PARSE_ERROR_FORMAT,
                                           timestamp,
                                           testing_array,
                                           counter,
                                           test_results)
                    return
                # Scheme OK, so now construct the query path to be sent to the
                # server in a HEAD request.
                url_path = url.path
                # Handle the case of "http://hostname.but.no.trailing.slash"
                if url_path == '':
                    url_path = '/'
                if url.query != '':
                    url_path += "?" + url.query
                if self._debug:
                    print('DEBUG: Counter:', counter,
                          'redirect_count:', redirect_count,
                          'url_str:', url_str, file=sys.stderr)

                # Determine the port to use for the connection.
                # Since 'https' contains 'http' as a prefix,
                # check for the former.
                if url.scheme.startswith('https'):
                    # For HTTPS, default to port 443.
                    port = url.port if url.port else 443
                    if self._debug:
                        print("DEBUG: Opening HTTPS connection to "
                              "host {}, port {}".format(url.hostname,
                                                        port),
                              file=sys.stderr)
                    reader, writer = yield from \
                        asyncio.open_connection(url.hostname,
                                                port, ssl=ssl_context)
                else:
                    # "Plain" HTTP request; port defaults to 80.
                    port = url.port if url.port else 80
                    if self._debug:
                        print("DEBUG: Opening HTTP connection to "
                              "host {}, port {}".format(url.hostname,
                                                        port),
                              file=sys.stderr)
                    reader, writer = yield from \
                        asyncio.open_connection(url.hostname, port)
                query = self.HEAD_QUERY_FORMAT.format(
                    url_path=url_path, url=url)
                if self._debug:
                    print("DEBUG:", counter, "Sending query string: ",
                          query,
                          file=sys.stderr)
                writer.write(query.encode("utf-8"))

                # Await and read the response.
                while True:
                    line = yield from reader.readline()
                    if not line:
                        # End of file read.
                        break
                    # readline() returns a bytes, so it must be decoded.
                    line = line.decode("utf-8").rstrip()
                    if line.startswith('<'):
                        # Oh dear, the server is now sending the page.
                        # This has been seen with an IIS/6.0 server.
                        break
                    if line:
                        # The next two lines are not used for now,
                        # but might be useful in the future.
                        # Apparently, there are some pages that are
                        # "soft 404s", i.e., they return a status code of
                        # (say) 200, but the content of the page is text
                        # which says "No such page" or the like.
                        # So in future, we may
                        # scrape pages to see if the page returned actually
                        # reports that the page is missing/deleted.
                        # if line.startswith('Content-Type'):
                        #     mType = line
                        if self._debug:
                            print('DEBUG:', counter, line, file=sys.stderr)
                        if line.startswith('HTTP/1.'):
                            mStatus = line
                        if line.startswith(('Location:', 'location:')):
                            location = line.split()[1]
                    else:
                        # Empty line was read; end of headers.
                        break
                if 'mStatus' not in locals():
                    # Made it through the loop without setting mStatus,
                    # which means (for some reason) we didn't get
                    # an HTTP status code.
                    self._handle_one_error(url_str_original,
                                           NO_STATUS_ERROR_FORMAT,
                                           timestamp,
                                           testing_array,
                                           counter,
                                           test_results)
                    return
                if mStatus:
                    # The status line is "HTTP/1.x 300 ....", so the status
                    # code is the second field after split,
                    # i.e., at position 1.
                    status_code = int(mStatus.split()[1])
                    # Now treat the different status codes as appropriate.
                    if status_code > 399:
                        # Status > 399 is an error, e.g., a "404".
                        self._handle_one_error(url_str_original,
                                               STATUS_ERROR_FORMAT.format(
                                                   mStatus),
                                               timestamp,
                                               testing_array,
                                               counter,
                                               test_results)
                        return
                    elif status_code == 301 or status_code == 302:
                        # Handle a redirection.
                        location = self.construct_absolute_path(url.scheme,
                                                                url.hostname,
                                                                url.port,
                                                                location)
                        if url_str != location:
                            # Follow a redirect.
                            url_str = location
                            # This is the only branch that falls through and
                            # leads to the next iteration of the for loop.
                        else:
                            # The redirected URL was the same as the original.
                            # Don't proceed any further.
                            self._handle_one_error(url_str_original,
                                                   REDIRECT_SAME_FORMAT,
                                                   timestamp,
                                                   testing_array,
                                                   counter,
                                                   test_results)
                            return
                    else:
                        # Success. This is indicated by deleting
                        # the corresponding element of testing_array.
                        try:
                            self._mark_status_and_timestamp(url_str_original,
                                                            SUCCESS,
                                                            timestamp)
                            del testing_array[counter]
                        except KeyError:
                            pass
                        return
            # "Successful" conclusion of the for loop. But this means
            # we have now followed too many redirects.
            self._handle_one_error(url_str_original,
                                   TOO_MANY_REDIRECTS_FORMAT.format(
                                       url_str),
                                   timestamp,
                                   testing_array,
                                   counter,
                                   test_results)
            return
        # An UnboundLocalError occurs if mStatus is tested without
        # having been set. Handle this using the catch-all handler
        # below.
        # except UnboundLocalError as e:
        #     _handle_one_error(result_list, error_count, testing_array,
        #                      creator,
        #                      EXCEPTION_FORMAT.format(doi_id,
        #                                              url_str, repr(e)),
        #                      counter)
        except asyncio.futures.CancelledError:
            # This is caused by _run_tests() cancelling the task
            # because of a timeout.
            pass
        except Exception as e:
            self._handle_one_error(url_str_original,
                                   EXCEPTION_FORMAT.format(e),
                                   timestamp,
                                   testing_array,
                                   counter,
                                   test_results)

    # Put backquotes around the column names.
    MARK_STATUS_QUERY = ("UPDATE registry_object_links SET " +
                         "`status` = %s, `last_checked` = %s " +
                         "WHERE `link` = %s;")

    def _mark_status_and_timestamp(self, link, status, timestamp):
        """Mark the status and timestamp for a link.

        Arguments:
        link -- The link whose status and timestamp are to be updated.
        status -- The status value to be stored in the status column.
        timestamp -- The timestamp to store in the last_checked column.
        """
        cur = self._conn.cursor()

        if self._debug:
            print("DEBUG: _mark_status_and_timestamp: link", link,
                  'status:', status, 'timestamp:', timestamp,
                  file=sys.stderr)
        try:
            result = cur.execute(self.MARK_STATUS_QUERY,
                                 (status, timestamp, link))
            if self._debug:
                print("DEBUG: _mark_status_and_timestamp query result:",
                      result, file=sys.stderr)
            self._conn.commit()
        finally:
            cur.close()

    # Format for text part of emailed reports.
    MESSAGE_FORMAT = """\
Report Run: {}

Broken Links Discovered: {}
Data Source Name: {}
Data Source ID: {}
"""

    # HTML wrapper for MESSAGE_FORMAT
    MESSAGE_HTML_WRAPPER_FORMAT = """\
<html>
  <head>Registry Object Broken Links Report</head>
  <body>
    <p>{}</p>
  </body>
</html>
"""

    LINK_RESULT_COLUMNS_FORMAT = ('Data source id,'
                                  'Data source name,'
                                  'Record title,'
                                  'Record URL,'
                                  'Link type,'
                                  'Link,'
                                  'Status,'
                                  'Error'
                                  '\n')

    # Format for message about missing client.
    MISSING_DATA_SOURCE_FORMAT = """Report from Registry Object checker.

There is a Registry Object in the registry_objects table with a data_source_id
which does not appear as a data_source_id in the data_sources table.
data_source_id: {}
    """

    def _process_results(self, data_sources, test_results,
                         data_source_id=None, admin_email=None):
        """Summarize the results, and print and email the results.

        Summarize the results of testing, print out the results,
        and email the results to whom it supposed to.

        The recipient of each email is determined as follows:

        1. If a value is specified for admin_email (using the "-e"
        command-line argument), then this address is
        used as the recipient of all outgoing mails. The admin_email
        parameter serves to override all other possible recipients.

        2. Otherwise (no admin_email was provided), was a data_source_id
        provided?

        2a. If a data_source_id was provided, then use the data
        source's email address as the recipient.

        2b. If no data_source_id was provided, then this is a report over all
        data sources. The value of params['sender_email] is used as the
        recipient.

        Arguments:
        data_sources -- The details of the client(s) of the DOIs.
        test_results -- The results of the tests.
        data_source_id -- A data_source_id, if one was specified, or None, if
            all data sources are to be reported.
        admin_email -- If specified, this is used as the recipient of all
            outgoing messages. If not specified, use the client's address,
            or fall back to the sender's address.
        """
        # Are we reporting on just one data source?
        is_one_data_source = data_source_id
        rol_list = []
        self._get_RO_links_for_reporting(rol_list, data_source_id)

        result_list = {}
        error_count = {}
        self._make_result_list_and_error_count(data_sources, test_results,
                                               rol_list,
                                               result_list, error_count)
        if ((data_source_id is not None) and
                error_count[data_source_id] == 0):
            # Success; one client's links were tested and all OK.
            data_source_title = data_sources[data_source_id]['title']

            recipient = self._get_email_recipient(
                data_sources, data_source_id, admin_email)

            message_time = datetime.datetime.now().strftime("%Y-%m-%d %H:%M")
            message_text = self.MESSAGE_FORMAT.format(message_time,
                                                      0,
                                                      str(data_source_title),
                                                      str(data_source_id))
            message_text_as_html = message_text.replace('\n', '\n    <br />')
            message_html = self.MESSAGE_HTML_WRAPPER_FORMAT.format(
                message_text_as_html)
            # self._insert_message_log(data_source_id,
            #                          message_text, 'SUCCESS')
            message_subject = \
                ("Broken Links Discovered for Registry Data Source: " +
                 data_source_title)
            self.send_one_email(recipient,
                                "RO.LINK.CHECKER",
                                message_subject,
                                message_text, message_html)
            self.print_text_or_html(message_text, message_text_as_html)
            # We're done; stop here. result_list is not empty, so
            # the following for loop would send another email.
            return
        # Loop over every data source.
        for data_source_id, message in result_list.items():
            try:
                data_source_title = data_sources[data_source_id]['title']
                client_broken_link_count = error_count[data_source_id]
                # Don't go any further for this data source if there
                # weren't any broken links. NB unlike in DOI.py,
                # we have to do this check, as here we _do_ get elements
                # in result_list for all data sources, even those
                # with no errors.
                if client_broken_link_count == 0:
                    continue
                message_time = datetime.datetime.now().strftime(
                    "%Y-%m-%d %H:%M")
                message_text = self.MESSAGE_FORMAT.format(
                    message_time,
                    str(client_broken_link_count),
                    str(data_source_title),
                    str(data_source_id))
                message_text_as_html = message_text.replace('\n',
                                                            '\n    <br />')
                message_html = self.MESSAGE_HTML_WRAPPER_FORMAT.format(
                    message_text_as_html)
                # self._insert_message_log(data_source_id, message_text,
                #                          'FAILURE')

                message_csv = self.LINK_RESULT_COLUMNS_FORMAT
                message_csv += message

                recipient = self._get_email_recipient(
                    data_sources, data_source_id, admin_email)
                if is_one_data_source:
                    # One data source has been checked,
                    # so print out the result on the console.
                    self.print_text_or_html(message_text,
                                            message_text_as_html)

                message_subject = \
                    ("Broken Links Discovered for Registry Data Source: " +
                     data_source_title)

                self.send_one_email(
                    recipient,
                    "RO.LINK.CHECKER",
                    message_subject,
                    message_text, message_html, message_csv)
            except KeyError:
                # There is no such owner_id, so data_sources[data_source_id]
                # failed. Send a message to the admin.
                if self._debug:
                    print("DEBUG: Going to send a missing client "
                          "email for data_source_id: ", data_source_id,
                          file=sys.stderr)
                message_text = self.MISSING_DATA_SOURCE_FORMAT.format(
                    data_source_id)
                message_text_as_html = message_text.replace('\n',
                                                            '\n    <br />')
                message_html = self.MESSAGE_HTML_WRAPPER_FORMAT.format(
                    message_text_as_html)
                self.send_one_email(
                    self._params['sender_email'],
                    "RO LINK CHECKER",
                    "RO ro has a link with a missing owner",
                    message_text, "")

    # Columns needed to get the links for reporting.
    RO_LINKS_REPORTING_COLUMNS = [
        'registry_object_id',
        'data_source_id',
        'link_type',
        'link',
        'status',
        'last_checked']
    # Columns needed from the registry_objects table for reporting.
    RO_REPORTING_COLUMNS = [
        'title',
        'slug']

    def _get_RO_links_for_reporting(self, rol_list, data_source_id=None):
        """Get all RO links to be reported.

        The rol_list array is updated in situ.

        Arguments:
        rol_list -- The array to be populated with RO link data
            from the database.
        data_source_id -- A data_source_id to use for searching the database,
            or None, if the ROs of all data sources are to be returned.
        """
        cur = self._conn.cursor()

        # Put backquotes around the column names.
        rol_columns_for_query = ["rol.`" + c + "`"
                                 for c in self.RO_LINKS_REPORTING_COLUMNS]
        ro_columns_for_query = ["ro.`" + c + "`"
                                for c in self.RO_REPORTING_COLUMNS]
        # No "DISTINCT" this time (cf. _get_RO_links_for_checking()).
        query = ("SELECT " +
                 ", ".join(rol_columns_for_query) +
                 ", " +
                 ", ".join(ro_columns_for_query) +
                 " FROM registry_object_links rol"
                 " JOIN registry_objects ro"
                 " WHERE rol.registry_object_id = ro.registry_object_id")
        if data_source_id is not None:
            query += " AND rol.`data_source_id`=" + str(data_source_id)
        query += " ORDER BY ro.registry_object_id;"
        if self._debug:
            print("DEBUG: get_RO_links query:", query, file=sys.stderr)
        cur.execute(query)
        all_columns = (self.RO_LINKS_REPORTING_COLUMNS +
                       self.RO_REPORTING_COLUMNS)
        for r in cur:
            # Turn the result tuple into a dict with the column names as keys
            rol = {k: v for k, v in zip(all_columns, r)}
            # If url is missing (NULL in the database), set it to
            # an empty string. This allows calling strip() on it
            # later (in _run_tests).
            if not rol['link']:
                rol['link'] = ""
            # Only add links that are of the right type.
            if self._is_link_to_be_checked(rol):
                rol_list.append(rol)
        cur.close()
        if self._debug:
            print("DEBUG: _get_RO_links_for_reporting rol_list:",
                  rol_list, file=sys.stderr)

    # This version can be used for debugging:
    # LINK_RESULT_FORMAT = (''
    #                       'Data source id: {},'
    #                       'Data source name: {},'
    #                       'Record title: {},'
    #                       'Record URL: {},'
    #                       'Link type: {},'
    #                       'Link: {},'
    #                       'Status: {},'
    #                       'Error: {}'
    #                       '\n')
    LINK_RESULT_FORMAT = ('{},'
                          '"{}",'
                          '"{}",'
                          '{},'
                          '{},'
                          '"{}",'
                          '{},'
                          '"{}"'
                          '\n')

    def _make_result_list_and_error_count(self, data_sources, test_results,
                                          rol_list, result_list, error_count):
        """Construct result_list and error_count from rol_list.

        Construct result_list and error_count based on rol_list
        and test_results.

        Arguments:
        data_sources -- The details of the client(s) of the DOIs.
        test_results -- The results of the tests.
        rol_list -- List of registry objects to be reported on.
        result_list -- Packed results of checking, by data_source_id.
        error_count -- Error counts of checking, by data_source_id.
        """
        for data_source_id in data_sources.keys():
            result_list[data_source_id] = ""
            error_count[data_source_id] = 0
        for rol in rol_list:
            if rol['status'] == 'BROKEN':
                data_source_id = rol['data_source_id']
                if rol['link'] in test_results:
                    test_result = test_results[rol['link']]
                else:
                    test_result = 'Test result not available'
                result_list[data_source_id] += self.LINK_RESULT_FORMAT.format(
                    data_source_id,
                    data_sources[data_source_id]['title'].replace('"', ''),
                    rol['title'].replace('"', ''),
                    self._link_to_record(rol),
                    rol['link_type'],
                    rol['link'],
                    rol['status'],
                    test_result.replace('"', ''),
                    )
                error_count[data_source_id] += 1

    def _link_to_record(self, registry_object):
        """Get the web address of a registry object.

        Given the details of a registry object, construct
        the link to its correspoinding page in the registry.

        Arguments:
        registry_object -- Details of one registry object; one
            element (a dict) of the rol_list list.  Must have
            "registry_object_id" and "slug" keys.
        """
        return (self._params['registry_prefix'] +
                registry_object['slug'] + "/" +
                str(registry_object['registry_object_id']))

    def _get_email_recipient(self, data_sources,
                             data_source_id,
                             admin_email):
        """Determine the recipient email address for a report.

        Arguments:
        data_sources -- The details of the client(s) of the DOIs.
        data_source_id -- A data_source_id, or None, if all data sources
           are being checked.
        admin_email -- If specified, this is used as the recipient of all
            outgoing messages. If not specified, use the client's address,
            or fall back to the sender's address.
        Return value:
        The recipient email address to use.
        """
        if admin_email:
            # admin_email overrides all other possibilities.
            recipient = admin_email
        elif ((data_source_id is not None) and
              'email' in data_sources[data_source_id] and
              data_sources[data_source_id]['email'].strip()):
            # There is a non-null email parameter for the data source
            recipient = data_sources[data_source_id]['email'].strip()
        else:
            # Fall back to sender_email parameter.
            recipient = self._params['sender_email']
        return recipient

    # Logging functions

    def _insert_message_log(self, owner_id, message, status):
        """Insert a log entry into the database's activity_log table.

        The activity is specified as "LINKCHECK".

        Arguments:
        owner_id -- The owner of the DOI. This value is used as the
            "data_source_id" column of the entry.
        message -- The value to use for the "message" column of the entry.
        status -- The value to use for the "status" column of the entry.
        """
        cursor = self._conn.cursor()
        sql = ("INSERT INTO activity_log "
               "(`data_source_id`, `message`, `activity`, `result`) "
               "values (%s, %s, %s, %s);")
        cursor.execute(sql, (owner_id, message, 'LINKCHECK', status))
        cursor.close()
        self._conn.commit()

    def _handle_one_error(self, link, status, timestamp,
                          testing_array, test_index, test_results):
        """Store details of one error.

        Arguments:
        url -- The url that caused the error.
        testing_array -- The dict containing the details of the current batch
            of tests.
        message -- The error message to be saved.
        test_index -- The key of testing_array corresponding to this test,
            or -1.  If the key is valid, the key/value pair will be removed
            from testing_array.
        test_results -- The dict containing the details of test results.
        """
        self._mark_status_and_timestamp(link, 'BROKEN', timestamp)
        test_results[link] = status
        try:
            # _run_tests calls this function with test_index = -1.
            del testing_array[test_index]
        except KeyError:
            pass


if __name__ == "__main__":
    print('This module can not be executed standalone.')
    sys.exit(1)
