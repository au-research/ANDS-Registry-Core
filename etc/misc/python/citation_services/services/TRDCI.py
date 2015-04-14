"""
Thomson Reuters Data Citation Index service for registry objects.
"""

"""
The TR DCI service relies on a table "registry_object_citations".

The service operates as follows:
1. Get the registry objects to look up from the database.
2. Look up the registry objects in TR DCI, updating each one's
   citation data in the database.


Data structures used throughout this module:

(roc = "registry object citation")
roc_list: list
  element: dict: a row from the registry_object_citations table.
    key: one of the column names in the registry_object_citations table.
      special key: "citation_result" is used internally to store
      the values returned from the TR DCI service.
    value: the value of that column


query db to get ROs
  select from registry_object_citations
    where service_provider is TRDCI
    and last_checked is more than update_frequency days ago
    optionally: where data_source_id = ...
in groups of batch_size:
  construct query to TRDCI
    for each ro, unpack query_terms to determine what to send to TR
  send query
  receive result
  unpack result, update db

Preliminary work for this service implementation was done by Melanie Barlow.
"""

import sys
import json
import urllib.request
import xml.dom.minidom
import xml.sax.saxutils

import pymysql

# The base module contains the BaseService class.
from . import base


class TRDCIService(base.BaseService):
    """Thomson Reuters Data Citation Index service for registry objects.
    """

    # service_url: "https://gateway.webofknowledge.com/gateway/Gateway.cgi"
    # username: "username"
    # password: "password"
    # service_id: "DRCI"
    # update_frequency: 7
    # batch_size: 50
    required_options = {'service_url', 'username', 'password',
                        'service_id', 'update_frequency', 'batch_size'}

    # The value of the service_provider column in the database
    SERVICE_PROVIDER = "TRDCI"

    # The value of the key used to store citation results in
    # each element of roc_list.
    CITATION_RESULT_KEY = 'citation_result'

    def do_update_citation_data(self):
        """Do the updating of citation data.
        """

        roc_list = []
        self._get_ROs_for_checking(roc_list, self._params['client_id'])
        self._fetch_citations(roc_list)
        self._update_citations_in_database(roc_list)
        if 'portal_database_name' in self._params:
            self._update_citation_counts_in_portal_database(roc_list)

    # Columns in the registry_object_citations table.
    # RO_CITATIONS_COLUMNS = [
    #     'id',
    #     'registry_object_id',
    #     'data_source_id',
    #     'service_provider',
    #     'query_terms',
    #     'citation_data',
    #     'last_checked']
    # Columns needed to get the links for querying.
    # registry_object_id and slug are needed for updating the portal database.
    RO_CITATIONS_CHECKING_COLUMNS = [
        'id',
        'registry_object_id',
        'slug',
        'query_terms']

    def _get_ROs_for_checking(self, roc_list, data_source_id=None):
        """Get all ROs to be looked up.

        The roc_list array is updated in situ.

        Arguments:
        roc_list -- The array to be populated with RO data
            from the database.
        data_source_id -- A data_source_id to use for searching the database,
            or None, if the ROs of all data sources are to be returned.
        """
        cur = self._conn.cursor()

        # First, compute the time to be used to set the last_checked
        # column of the records that get updated. For the purposes of
        # this calculation, select the current time, minus one
        # hour. This is a simple workaround to allow this program to
        # be run regularly at the same time each update_frequency
        # days. If, instead, the update of the records were done using
        # an UPDATE statement that used e.g., NOW(), for the
        # last_checked value, then the next time this module is run
        # (i.e., in update_frequency days), those updated records
        # would not yet have "expired".
        query = 'SELECT SUBDATE(NOW(),INTERVAL 1 HOUR);'
        cur.execute(query)
        for r in cur:
            self._timestamp = r

        # From the database:
        #  select the columns specified in RO_CITATIONS_CHECKING_COLUMNS
        #  filter on:
        #   service_provider is the value of SERVICE_PROVIDER
        #   last_checked is at least last_checked days ago
        #   if data_source_id was specified, then only select
        #    records from that data source.

        # Put backquotes around the column names.
        # Special treatment for registry_object_id, as it appears
        # in both of the two tables being joined.
        columns_for_query = [
            ("`" + c + "`").replace(
                'registry_object_id',
                'roc`.`registry_object_id')
            for c in self.RO_CITATIONS_CHECKING_COLUMNS]
        columns_for_query = columns_for_query
        query = ("SELECT " + ", ".join(columns_for_query) +
                 " FROM registry_object_citations roc, registry_objects ro" +
                 " WHERE roc.registry_object_id = ro.registry_object_id" +
                 " AND `service_provider` = '" +
                 self.SERVICE_PROVIDER +
                 "' AND `last_checked` < NOW() - INTERVAL " +
                 str(self._params['update_frequency']) + " DAY")
        if data_source_id is not None:
            query += " AND `data_source_id`=" + str(data_source_id)
        query += ";"
        if self._debug:
            print("DEBUG: _get_ROs_for_checking query:", query,
                  file=sys.stderr)
        cur.execute(query)
        for r in cur:
            # Turn the result tuple into a dict with the column names as keys
            roc = {k: v for k, v in
                   zip(self.RO_CITATIONS_CHECKING_COLUMNS, r)}
            roc_list.append(roc)
        cur.close()
        if self._debug:
            print("DEBUG: _get_ROs_for_checking roc_list:",
                  roc_list, file=sys.stderr)

    def _fetch_citations(self, roc_list):
        """Fetch citation data from TR DCI.

        Given the registry objects to be looked up, query the TR DCI
        service. Update roc_list with the returned citation data.

        Arguments:
        roc_list -- The array containing details of the records
            to be looked up in the TR DCI service.

        """
        roc_index = 0
        roc_length = len(roc_list)
        while roc_index < roc_length:
            self._fetch_citations_for_one_batch(
                roc_list, roc_index)
            roc_index += int(self._params['batch_size'])
        if self._debug:
            print("DEBUG: _fetch_citations_and_update: " +
                  "after fetching, roc_list:", roc_list,
                  file=sys.stderr)

    # Template for requests to be sent to TR DCI
    TRDCI_REQUEST_TEMPLATE = """
    <request xmlns='http://www.isinet.com/xrpc42' src='app.id=ANDS'>
      <fn name='LinksAMR.retrieve'>
        <list>
          <!-- WHO'S REQUESTING -->
          <map>
            <val name='username'>{0}</val>
            <val name='password'>{1}</val>
          </map>
          <!-- WHAT'S REQUESTED -->
          <map>
            <list name='{2}'>
              <val>timesCitedAllDB</val>
              <val>uid</val>
              <val>doi</val>
              <val>sourceURL</val>
              <val>citingArticlesAllDBURL</val>
              <val>repositoryLinkURL</val>
            </list>
          </map>
          <!-- LOOKUP DATA -->
          {3}
        </list>
      </fn>
    </request>
    """

    def _fetch_citations_for_one_batch(self, roc_list, roc_index):
        """Fetch citation data from TR DCI and update the database.

        Look up one batch of registry objects in the TR DCI
        service. The batch to be looked up starts at index
        roc_index and has size batch_size.

        roc_list is updated with the results in situ.

        Arguments:
        roc_list -- The array containing details of the batch of records
            to be looked up in the TR DCI service.
        roc_index -- The lower index of the batch of records in roc_list.
        """
        if self._debug:
            print("DEBUG: _fetch_citations_for_one_batch: " +
                  "roc_index:", roc_index,
                  file=sys.stderr)
        citation_requests = self._create_citation_requests(roc_list,
                                                           roc_index)
        # Username, password, and service_id are escaped before
        # inserting into the template. The search terms are escaped
        # already because of the use of the xml.dom.minidom module.
        request_XML = self.TRDCI_REQUEST_TEMPLATE.format(
            xml.sax.saxutils.escape(self._params['username']),
            xml.sax.saxutils.escape(self._params['password']),
            xml.sax.saxutils.escape(self._params['service_id']),
            citation_requests.toprettyxml()
            )
        if self._debug:
            print("DEBUG: _fetch_citations_for_one_batch: " +
                  "request_XML:", request_XML,
                  file=sys.stderr)
        # Create request connection object
        request_conn = urllib.request.Request(self._params['service_url'])
        # Explicitly add a Content-Type header to notify that
        # this is an XML request; without this,
        # the Python library adds a Content-Type header with
        # "application/x-www-form-urlencoded" instead.
        request_conn.add_header('Content-Type',
                                'application/xml;encoding=utf-8')
        response = urllib.request.urlopen(request_conn,
                                          request_XML.encode('utf-8'))

        result = response.read()
        if self._debug:
            print("DEBUG: _fetch_citations_for_one_batch: " +
                  "result:", result,
                  file=sys.stderr)
        # Now update roc_list with the results.
        result_DOM = xml.dom.minidom.parseString(result)
        for map_element in result_DOM.getElementsByTagName('map'):
            # Only worry about map elements that have a name attribute
            # beginning with "cite_".
            if not (map_element.hasAttribute('name') and
                    map_element.getAttribute('name').startswith(
                        self.CITE_BEGINNING)):
                continue
            if self._debug:
                print("DEBUG: _fetch_citations_for_one_batch: " +
                      "found map element with name element:",
                      map_element.getAttribute('name'),
                      file=sys.stderr)
            cite_index = int(map_element.getAttribute('name')[
                self.CITE_BEGINNING_LENGTH:])
            map_dict = dict()
            for val_element in map_element.getElementsByTagName('val'):
                name_attr = val_element.getAttribute('name')
                text_value = self._get_text_of_element(val_element.childNodes)
                map_dict[name_attr] = text_value
            roc_list[cite_index][self.CITATION_RESULT_KEY] = map_dict
        pass

    def _get_text_of_element(self, nodelist):
        """Extract the combined text values from nodelist.

        This is based on the getText() function given as part of an
        example in the xml.dom.minidom documentation in the Python
        Library Reference.

        Note that TR return both text and CDATA nodes, so two node
        types must be tested for.

        Arguments:
        nodelist -- The array of nodes containing text to be extracted.
        Return value:
        The text contained in nodelist.

        """
        rc = []
        for node in nodelist:
            if (node.nodeType == node.TEXT_NODE or
                    node.nodeType == node.CDATA_SECTION_NODE):
                rc.append(node.data)
        return ''.join(rc)

    # Format of the attribute values to use on map elements
    # in the request.
    CITE_TEMPLATE = 'cite_{0}'

    # Like CITE_TEMPLATE, but used to match attribute values
    # returned by the TR DCI service.
    CITE_BEGINNING = 'cite_'
    # Like CITE_TEMPLATE, but used to match attribute values
    # returned by the TR DCI service.
    CITE_BEGINNING_LENGTH = len(CITE_BEGINNING)

    def _create_citation_requests(self, roc_list, roc_index):
        """Convert batch of records into XML needed for the service.

        The use of the xml.dom.minidom module ensures that the
        necessary escaping (i.e., of &, <, >) is done before
        insertion into the query sent to the TR DCI service.

        Code based on work done by Melanie Barlow.

        Arguments:
        roc_list -- The array containing details of the batch of records
            to be looked up in the TR DCI service.
        roc_index -- The lower index of the batch of records in roc_list.
        Return value:
        The XML to be included in the query sent to TR DCI.
        """
        DOM_implementation = xml.dom.minidom.getDOMImplementation()

        map_document = DOM_implementation.createDocument(None, 'map', None)
        map_root = map_document.documentElement

        cite_upper_bound = min(roc_index + int(self._params['batch_size']),
                               len(roc_list))

        for cite_counter in range(roc_index, cite_upper_bound):
            article = roc_list[cite_counter]
            map_node = map_document.createElement('map')
            map_attribute = map_document.createAttribute('name')
            map_attribute.value = self.CITE_TEMPLATE.format(cite_counter)
            cite_counter += 1

            map_node.setAttributeNode(map_attribute)
            map_root.appendChild(map_node)

            if self._debug:
                print("DEBUG: _create_citation_rquests: " +
                      "article['query_terms']:", article['query_terms'],
                      file=sys.stderr)
            for query_key, query_value in json.loads(
                    article['query_terms']).items():
                subnode = map_document.createElement(
                    'list' if query_key == 'authors' else 'val')
                subattribute = map_document.createAttribute('name')
                subattribute.value = query_key
                subnode.setAttributeNode(subattribute)

                map_node.appendChild(subnode)

                if query_key == 'authors':
                    itemList = query_value.split('|')
                    for item in itemList:
                        print(item)
                        itemNode = map_document.createElement('val')
                        itemText = map_document.createTextNode(item)
                        itemNode.appendChild(itemText)
                        subnode.appendChild(itemNode)
                else:
                    subText = map_document.createTextNode(
                        query_value)
                    subnode.appendChild(subText)
        return map_root

    # Database query template for updating the registry's
    # registry_object_citations table.
    ROC_QUERY_TEMPLATE = ("UPDATE registry_object_citations SET " +
                          " `citation_data` = %s" +
                          ", `status` = %s" +
                          ", `last_checked` = %s" +
                          " WHERE `id` = %s;")

    def _update_citations_in_database(self, roc_list):
        """Update the citation_data column in the database.

        Update the citation_data column in the database based
        on the citation data received from TR DCI.

        Arguments:
        roc_list -- The array containing the batch of records
            to be updated, with the citation results returned from the
            TR DCI service.
        """
        cur = self._conn.cursor()

        for r in roc_list:
            # if self._debug:
            #     print("DEBUG: _update_citations_in_database: " +
            #           "query:", query,
            #           file=sys.stderr)
            if ('message' in r[self.CITATION_RESULT_KEY] and
                r[self.CITATION_RESULT_KEY]['message'] ==
                    'No Result Found'):
                status = 'NOT_FOUND'
                citation_data = ''
            else:
                status = 'SUCCESS'
                citation_data = json.dumps(r[self.CITATION_RESULT_KEY])
            cur.execute(self.ROC_QUERY_TEMPLATE, [citation_data, status,
                                                  self._timestamp, r['id']])
        cur.close()
        self._conn.commit()

    # Database query template for updating the portal record_stats table.
    PORTAL_UPDATE_TEMPLATE = ("UPDATE record_stats SET " +
                              " `cited` = %s" +
                              " WHERE `ro_id` = %s;")
    # Database query template for inserting into the portal
    # record_stats table.
    PORTAL_INSERT_TEMPLATE = ("INSERT INTO record_stats " +
                              "(ro_id,ro_slug,viewed,cited,accessed) " +
                              "VALUES (" +
                              "%s,%s,0,%s,0);")

    def _update_citation_counts_in_portal_database(self, roc_list):
        """Update the record_stats table of the portal database.

        For those records for which we received citation counts
        from TR DCI, update the corresponding entry in the
        record_stats column in the portal database.

        If there is not yet a record for a registry object in the
        record_stats table, insert one. Note well, the only other
        place in the code that rows are added to record_stats is
        in the stat() function in
        applications/portal/registry_object/models/_ro.php.
        Changes there and here must be synchronized!

        Arguments:
        roc_list -- The array containing the batch of records
            to be updated, with the citation results returned from the
            TR DCI service.

        """
        portal_database_connection = self.open_portal_db_connection()
        try:
            cur = portal_database_connection.cursor()

            for r in roc_list:
                if ('timesCitedAllDB' in r[self.CITATION_RESULT_KEY]):
                    if self._debug:
                        print("DEBUG:",
                              "_update_citation_counts_in_portal_database:",
                              "r['registry_object_id']:",
                              r['registry_object_id'],
                              "timesCitedAllDB:",
                              r[self.CITATION_RESULT_KEY]
                              ['timesCitedAllDB'],
                              file=sys.stderr)
                    cur.execute(self.PORTAL_UPDATE_TEMPLATE,
                                [r[self.CITATION_RESULT_KEY]
                                 ['timesCitedAllDB'],
                                 r['registry_object_id']])
                    if cur.rowcount == 0:
                        # The row is missing, so insert it
                        cur.execute(self.PORTAL_INSERT_TEMPLATE,
                                    [r['registry_object_id'],
                                     r['slug'],
                                     r[self.CITATION_RESULT_KEY]
                                     ['timesCitedAllDB']])
            cur.close()
            portal_database_connection.commit()
        finally:
            portal_database_connection.close()
        pass

    def open_portal_db_connection(self):
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
        try:
            return pymysql.connect(
                host=self._params['database_host'],
                user=self._params['database_user'],
                passwd=self._params['database_password'],
                db=self._params['portal_database_name'])
        except Exception as e:
            print("Database Exception:", e)
            sys.exit(1)

    # Logging functions

    def _insert_message_log(self, owner_id, message, status):
        """Insert a log entry into the database's activity_log table.

        The activity is specified as "TRDCI".

        Arguments:
        owner_id -- The owner of the RO. This value is used as the
            "data_source_id" column of the entry.
        message -- The value to use for the "message" column of the entry.
        status -- The value to use for the "status" column of the entry.
        """
        cursor = self._conn.cursor()
        sql = ("INSERT INTO activity_log "
               "(`data_source_id`, `message`, `activity`, `result`) "
               "values (%s, %s, %s, %s);")
        cursor.execute(sql, (owner_id, message, 'TRDCI', status))
        cursor.close()
        self._conn.commit()


if __name__ == "__main__":
    print('This module can not be executed standalone.')
    sys.exit(1)
