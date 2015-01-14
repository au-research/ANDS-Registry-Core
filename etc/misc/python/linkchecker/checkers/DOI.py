"""
Link checker for ANDS DOIs.
"""

"""
Data structures used throughout this module:

client_list: dict (despite the name, grr)
  key: int: client_id
  value: tuple: The row the doi_client table
      that has client_id as its key.

doi_list: list
  element: tuple: a row from the doi_object table.

testing_array: dict (despite the name, grr)
  key: int: An index into the doi_list array.
  value: dict: Details of the link to be tested.
    There are three key/value pairs in each dictionary,
    taken from the corresponding tuple in doi_list.
    key: "url_str"
    value: The value of the "url" column (whitespace stripped).
    key: "creator"
    value: The value of the "client_id" column.
    key: "doi_id"
    value: The value of the "doi_id" column (whitespace stripped).

result_list: dict
  key: int: client_id
  value: str: text containing a list of the broken links
    for this client (for insertion into an email going to the client).

error_count: dict
  key: int: client_id
  value: int: The number of errors encounted when checking the links
    belonging to the client.
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


class DOIChecker(base.BaseChecker):
    """Checker for DOIs.
    """

    def do_link_checking(self):
        """Do the link checking.
        """
        client_list = {}
        self._get_client_list(client_list, self._params['client_id'])

        result_list = {}
        error_count = {}

        self._run_tests(self._params['ssl_context'],
                        self._params['client_id'],
                        self._params['admin_email'], client_list,
                        int(self._params['link_timeout']),
                        int(self._params['batch_size']),
                        result_list, error_count)
        self._process_result_lists(client_list, result_list, error_count,
                                   self._params['client_id'],
                                   self._params['admin_email'])

    # All the columns in the doi_client table, in order.
    DOI_CLIENT_COLUMNS = """\
      `client_id`,
      `client_name`,
      `client_contact_name`,
      `ip_address`,
      `app_id`,
      `created_when`,
      `client_contact_email`,
      `datacite_prefix`,
      `shared_secret`
    """

    def _get_client_list(self, client_list, client_id):
        """Get client information for DOIs.

        Get client information for generating a personalised record
        for each test run.
        Arguments:
        client_list -- The dictionary to be populated with the results
            of the database query.
        client_id -- A client_id to use for searching the database,
            or None, if all clients are to be returned.
        """
        cur = self._conn.cursor()
        query = "SELECT " + self.DOI_CLIENT_COLUMNS + " FROM doi_client"
        if client_id is not None:
            cur.execute(query + " where `client_id`=" + str(client_id) + ";")
        else:
            cur.execute(query + ";")
        for r in cur:
            client_list[r[0]] = r
            if self._debug:
                print("DEBUG: Assigning client_list[{}] = {}".format(
                    r[0], r), file=sys.stderr)
        cur.close()

    def _run_tests(self, ssl_context, client_id, admin_email, client_list,
                   link_timeout, batch_size,
                   result_list, error_count):
        """
        Arguments:
        ssl_context -- The SSL context to use when making HTTP requests.
        client_id -- A client_id to use for searching the database,
        admin_email -- If not None, the email address to use as
            recipient of all outgoing messages.
        client_list -- The details of the client(s) of the DOIs.
        link_timeout -- Timeout to use, in seconds.
        batch_size -- Maximum number of concurrent link checks.
        result_list -- The results of the tests.
        error_count -- The errors resulting from the tests.
        """

        doi_list = []
        testing_array = {}

        self._get_DOI_links(doi_list, client_id)

        REPORT_HEADER = "Number of URLs to be tested: " + str(len(doi_list))
        self.print_text_or_html(REPORT_HEADER,
                                REPORT_HEADER + "\n<br />")
        socket.setdefaulttimeout(link_timeout)
        loop = asyncio.get_event_loop()
        # Sleep 1 before getting started. (Why?)
        time.sleep(1)
        TIMEOUT_ERROR_FORMAT = 'Error DOI_ID: {} URL: {} CONNECTION TIMEOUT'
        # The variable "batch_number" iterates over batches
        # of size batch_size.
        batch_number = 0
        # Store the length of doi_list for convenience
        len_doi_list = len(doi_list)
        while len_doi_list > (batch_number * batch_size):
            task_array = []
            # This range() iterates over a range of size (at most) batch_size
            for i in range(batch_number * batch_size,
                           min((batch_number + 1) * batch_size,
                               len_doi_list)):
                if self._debug:
                    print("DEBUG: i =", i, "; doi_list[i] =", doi_list[i],
                          file=sys.stderr)
                testing_array[i] = {"url_str": doi_list[i][13].strip(),
                                    "creator": doi_list[i][11],
                                    "doi_id": doi_list[i][0].strip()}
                task_array.append(asyncio.async(
                    self._check_URL_resource(ssl_context,
                                             doi_list[i],
                                             i,
                                             result_list,
                                             error_count,
                                             testing_array)))
            try:
                loop.run_until_complete(asyncio.wait(task_array,
                                                     timeout=link_timeout))
                # If a test is successful, the corresponding entry in
                # testing_array is deleted.
                # So when run_until_complete returns, the entries
                # remaining in testing_array are all timeouts.
                for k, v in testing_array.items():
                    self._handle_one_error(result_list, error_count,
                                           testing_array,
                                           v['creator'],
                                           TIMEOUT_ERROR_FORMAT.format(
                                               v['doi_id'],
                                               v['url_str']),
                                           -1)
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

    DOI_OBJECTS_COLUMNS = """\
      `doi_id`,
      `publisher`,
      `publication_year`,
      `language`,
      `version`,
      `updated_when`,
      `status`,
      `identifier_type`,
      `rights`,
      `last_metadata_update`,
      `last_metadata_version`,
      `client_id`,
      `created_who`,
      `url`,
      `created_when`,
      `datacite_xml`
    """

    def _get_DOI_links(self, doi_list, client_id=None):
        """Get all production DOIs to be tested.

        Production DOIs are those which have a status other than
        "REQUESTED", and which have a doi_id beginning with "10.4".

        The doi_list array is updated in situ.

        Arguments:
        doi_list -- The array to be populated with DOI data from the database.
        client_id -- A client_id to use for searching the database,
            or None, if the DOIs of all clients are to be returned.
        """
        cur = self._conn.cursor()
        query = ("SELECT " + self.DOI_OBJECTS_COLUMNS +
                 " FROM doi_objects WHERE ")
        if client_id is not None:
            query += "`client_id`=" + str(client_id) + " AND "
        query += ("`identifier_type`='DOI'"
                  " AND `status`!='REQUESTED'"
                  " AND `doi_id` LIKE '10.4%';")
        if self._debug:
            print("DEBUG: _get_DOI_links query:", query, file=sys.stderr)
        cur.execute(query)
        for r in cur:
            # If url is missing (NULL in the database), set it to
            # an empty string. This allows calling strip() on it
            # later (in _run_tests).
            if not r[13]:
                l = list(r)
                l[13] = ""
                r = tuple(l)
            doi_list.append(r)
        cur.close()

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
                            r, counter,
                            result_list, error_count, testing_array):
        """Check one URL resource.

        Request the header for each resource and try to determine
        if it is resolvable.
        Record a log entry if an exception occurs, or the server
        returns a 400/500 error.

        Arguments:
        ssl_context -- The SSL context to use when making HTTP requests.
        r -- The tuple containing a row from the doi_object table with
            the details of the link to be tested.
        counter -- The key of testing_array corresponding to this test.
            If the key is valid, and the link is valid, the key/value pair
            will be removed from testing_array.
        result_list -- The dict containing the results of testing.
        error_count -- The dict containing the error counts.
        testing_array -- The dict containing the details of the current batch
            of tests.
        """
        # Hmm, why did we put the data in testing_array?
        # See _run_tests for the same code.
        url_str = url_str_original = r[13].strip()
        creator = r[11]
        doi_id = r[0].strip()

        SCHEME_NOT_HTTP_FORMAT = ('Error: Scheme is not http(s): '
                                  'DOI_ID: {} URL: {}')
        URL_PARSE_ERROR_FORMAT = ('Error: Parsing URL failed: '
                                  'DOI_ID: {} URL: {}')
        NO_STATUS_ERROR_FORMAT = ('Error: Server did not return an '
                                  'HTTP status code')
        STATUS_ERROR_FORMAT = '4/500s: DOI_ID: {} URL: {} Status {}'
        REDIRECT_SAME_FORMAT = ('Error: Redirect URL same as original: '
                                'DOI_ID: {} URL: {}')
        EXCEPTION_FORMAT = 'Error: DOI_ID: {} URL: {} exception {}'
        TOO_MANY_REDIRECTS_FORMAT = ('Error: too many redirects: '
                                     'DOI_ID: {} ORIGINAL URL: {} '
                                     'FINAL URL: {}')
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
                    self._handle_one_error(result_list, error_count,
                                           testing_array,
                                           creator,
                                           SCHEME_NOT_HTTP_FORMAT.format(
                                               doi_id,
                                               url_str),
                                           counter)
                    return
                if not url.hostname:
                    # Something wrong with the parsing of the URL,
                    # possibly "http:/only-one-slash.com".
                    self._handle_one_error(result_list, error_count,
                                           testing_array,
                                           creator,
                                           URL_PARSE_ERROR_FORMAT.format(
                                               doi_id,
                                               url_str),
                                           counter)
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
                    self._handle_one_error(result_list, error_count,
                                           testing_array,
                                           creator,
                                           NO_STATUS_ERROR_FORMAT,
                                           counter)
                    return
                if mStatus:
                    # The status line is "HTTP/1.x 300 ....", so the status
                    # code is the second field after split,
                    # i.e., at position 1.
                    status_code = int(mStatus.split()[1])
                    # Now treat the different status codes as appropriate.
                    if status_code > 399:
                        # Status > 399 is an error, e.g., a "404".
                        self._handle_one_error(result_list, error_count,
                                               testing_array,
                                               creator,
                                               STATUS_ERROR_FORMAT.format(
                                                   doi_id,
                                                   url_str,
                                                   mStatus),
                                               counter)
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
                            self._handle_one_error(
                                result_list, error_count,
                                testing_array,
                                creator,
                                REDIRECT_SAME_FORMAT.format(
                                    doi_id,
                                    url_str),
                                counter)
                            return
                    else:
                        # Success. This is indicated by deleting
                        # the corresponding element of testing_array.
                        try:
                            del testing_array[counter]
                        except KeyError:
                            pass
                        return
            # "Successful" conclusion of the for loop. But this means
            # we have now followed too many redirects.
            self._handle_one_error(result_list, error_count, testing_array,
                                   creator,
                                   TOO_MANY_REDIRECTS_FORMAT.format(
                                       doi_id,
                                       url_str_original,
                                       url_str),
                                   counter)
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
            self._handle_one_error(result_list, error_count, testing_array,
                                   creator,
                                   EXCEPTION_FORMAT.format(
                                       doi_id, url_str, repr(e)),
                                   counter)

    # Format for text part of emailed reports.
    MESSAGE_FORMAT = """\
Report Run: {}

Broken Links Discovered: {}
Client Name: {}
Client App ID: {}"""

    # HTML wrapper for MESSAGE_FORMAT
    MESSAGE_HTML_WRAPPER_FORMAT = """\
<html>
  <head>Cite My Data Broken Links Report</head>
  <body>
    <p>{}</p>
  </body>
</html>
"""

    # Format for message about missing client.
    MISSING_CLIENT_FORMAT = """Report from DOI checker.

There is a DOI in the doi_objects table with an owner_id
which does not appear as a client_id in the doi_client table.
owner_id: {}
    """

    def _process_result_lists(self, client_list, result_list, error_count,
                              client_id=None, admin_email=None):
        """Summarize the errors, log and email the results.

        Summarize the logs, create appropriate headings, log an entry, and
        email the content to whom it supposed to.

        An entry is logged to the database for all clients with an
        error in the link.

        The recipient of each email is determined as follows:

        1. If a value is specified for admin_email (using the "-e"
        command-line argument), then this address is
        used as the recipient of all outgoing mails. The admin_email
        parameter serves to override all other possible recipients.

        2. Otherwise (no admin_email was provided), was a client_id
        provided?

        2a. If a client_id was provided, then use the client's email address
        as the recipient.

        2b. If no client_id was provided, then this is a report over all
        clients. The value of params['sender_email] is used as the
        recipient.

        Arguments:
        client_list -- The details of the client(s) of the DOIs.
        result_list -- The results of the tests.
        error_count -- The errors resulting from the tests.
        client_id -- A client_id, if one was specified, or None, if
            all clients are to be reported.
        admin_email -- If specified, this is used as the recipient of all
            outgoing messages. If not specified, use the client's address,
            or fall back to the sender's address.
        """
        if len(result_list) == 0 and (client_id is not None):
            # Success; one client's links were tested and all OK.
            client_app_id = client_list[client_id][4]
            client_name = client_list[client_id][1]

            if admin_email:
                # admin_email overrides all other possibilities.
                recipient = admin_email
            else:
                recipient = client_list[client_id][6]

            message_time = datetime.datetime.now().strftime("%Y-%m-%d %H:%M")
            message_text = self.MESSAGE_FORMAT.format(message_time,
                                                      0,
                                                      str(client_name),
                                                      str(client_app_id))
            message_text_as_html = message_text.replace('\n', '\n    <br />')
            message_html = self.MESSAGE_HTML_WRAPPER_FORMAT.format(
                message_text_as_html)
            self._insert_message_log(client_id, message_text, 'SUCCESS')
            message_subject = \
                ("Broken Links Discovered for Cite My Data Client: " +
                 client_name)
            self.send_one_email(recipient,
                                "DOI.LINK.CHECKER",
                                message_subject,
                                message_text, message_html)
            self.print_text_or_html(message_text, message_text_as_html)
        # Loop over every client with at least one error.
        for owner_id, message in result_list.items():
            try:
                client_app_id = client_list[owner_id][4]
                client_name = client_list[owner_id][1]
                client_broken_link_count = error_count[owner_id]
                message_time = datetime.datetime.now().strftime(
                    "%Y-%m-%d %H:%M")
                message_text = self.MESSAGE_FORMAT.format(
                    message_time,
                    str(client_broken_link_count),
                    str(client_name),
                    str(client_app_id))
                message_text += '\nDOIs with broken links:\n' + message
                message_text_as_html = message_text.replace('\n',
                                                            '\n    <br />')
                message_html = self.MESSAGE_HTML_WRAPPER_FORMAT.format(
                    message_text_as_html)
                self._insert_message_log(owner_id, message_text, 'FAILURE')
                if client_id is not None:
                    # A client_id was specified, so print out the result
                    # on the console.
                    self.print_text_or_html(message_text,
                                            message_text_as_html)
                # Determine the email recipient.
                if admin_email:
                    # admin_email overrides all other possibilities.
                    recipient = admin_email
                elif client_id is not None:
                    # No admin_email specified, but there is a client_id.
                    recipient = client_list[owner_id][6]
                else:
                    # Fall back to using the sender as the recipient.
                    recipient = self._params['sender_email']
                message_subject = \
                    ("Broken Links Discovered for Cite My Data Client: " +
                     client_name)

                self.send_one_email(
                    recipient,
                    "DOI.LINK.CHECKER",
                    message_subject,
                    message_text, message_html)
            except KeyError:
                # There is no such owner_id, so client_list[owner_id]
                # failed. Send a message to the admin.
                if self._debug:
                    print("DEBUG: Going to send a missing client "
                          "email for owner_id: ", owner_id,
                          file=sys.stderr)
                message_text = self.MISSING_CLIENT_FORMAT.format(
                    owner_id)
                message_text_as_html = message_text.replace('\n',
                                                            '\n    <br />')
                message_html = self.MESSAGE_HTML_WRAPPER_FORMAT.format(
                    message_text_as_html)
                self.send_one_email(
                    self._params['sender_email'],
                    "DOI LINK CHECKER",
                    "DOI doi_objects has a link with a missing owner",
                    message_text, "")

    # Logging functions

    def _insert_message_log(self, owner_id, message, status):
        """Insert a log entry into the database's activity_log table.

        The activity is specified as "LINKCHECK".

        Arguments:
        owner_id -- The owner of the DOI. This value is used as the
            "client_id" column of the entry.
        message -- The value to use for the "message" column of the entry.
        status -- The value to use for the "status" column of the entry.
        """
        cursor = self._conn.cursor()
        sql = ("INSERT INTO activity_log "
               "(`client_id`, `message`, `activity`, `result`) "
               "values (%s, %s, %s, %s);")
        cursor.execute(sql, (owner_id, message, 'LINKCHECK', status))
        cursor.close()
        self._conn.commit()

    def _handle_one_error(self, result_list, error_count, testing_array,
                          owner_id, message, test_index):
        """Store details of one error.

        This maintains a summary for each client.

        Arguments:
        result_list -- The dict for storing error messages, per client_id.
        error_count -- The dict for storing the count of the number
            of errors, per client_id.
        testing_array -- The dict containing the details of the current batch
            of tests.
        owner_id -- The creator (client_id) of the link.
        message -- The error message to be saved.
        test_index -- The key of testing_array corresponding to this test,
            or -1.  If the key is valid, the key/value pair will be removed
            from testing_array.
        """
        try:
            result_list[owner_id] = (result_list[owner_id] +
                                     '\n' +
                                     message)
            error_count[owner_id] = error_count[owner_id] + 1
        except KeyError:
            result_list[owner_id] = message
            error_count[owner_id] = 1
        try:
            # _run_tests calls this function with test_index = -1.
            del testing_array[test_index]
        except KeyError:
            pass


if __name__ == "__main__":
    print('This module can not be executed standalone.')
    sys.exit(1)
