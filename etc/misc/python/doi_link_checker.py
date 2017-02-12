# DOI LINK CHECKER
# python (3.4+) script
# requires asyncio modules for assynchronous processing
# Task to perform: 
# extracts the urls for each DOI objects from the database
# tests them in a batch of 100 (for limiting the creation of simultanious connection)
# if error occures logs a report contaning the error
# creates a report for each client and log the result in the database
# send email to the admin person with the result 
#
# usage 
# /usr/local/bin/python3 doi_link_test.py -c <client_id> -e <admin_email_addr>
#
# if client id given only DOIs for that client will be tested 
#
# email will be sent to either the given email or the contact email for the client as registered in the DOI db
# if client idea not present 
# eg: /usr/local/bin/python3 doi_link_test.py
# all DOIs will be tested 
# and for each client with broken url a report will be genearted and sent to the default admin or the provided <admin_email_addr>
#
# Author: u4187959
# created 20/02/2017
# TODO: way too many hard-coded variables will do a version to read the PHP config files and get the values from there
# 

import asyncio
import urllib.request
import pymysql
import array
import sys
import getopt
import ssl
import datetime
import smtplib
import socket
import myconfig
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText




#
# handleErrors
# creates a summary for each client
# keep a number of entries 
# 


def handleErrors(owner_id, message, counter):

	try:
		resultList[owner_id] = resultList[owner_id] + '<br/>'+ message
		errorCount[owner_id] = errorCount[owner_id] + 1
	except KeyError:
		resultList[owner_id] = message
		errorCount[owner_id] = 1
	try:
		del testingArray[counter]
	except KeyError:
		pass

#
# Inserts a log entry to the given client_id into the database
#
#
def insertMessageLog(owner_id, message, status):

	cur = conn.cursor()
	sql = "INSERT INTO activity_log (`client_id`, `message`, `activity`, `result`) values (%s, %s, %s, %s);"
	cur.execute(sql, (owner_id, message, 'LINKCHECK', status))
	cur.close()
	conn.commit()

#
# sends an email to the given emailAaddr with the message as content
# 
#

def sendEmail(emailAddr, clientTitle, message):
	try:
		mySmtp = smtplib.SMTP('localhost')
		me = myconfig.adminEmailAddr
		msg = MIMEMultipart('alternative')
		msg['Subject'] = "Broken Links Discovered for Cite My Data Client: " + clientTitle
		msg['From'] = "DOI.LINK.CHECKER"
		msg['To'] = emailAddr
		text = message
		html = """\
		<html>
		  <head>Cite My Data Broken Links Report</head>
		  <body>
		    <p>"""+ message + """</p>
		  </body>
		</html>
		"""
		part1 = MIMEText(text, 'plain')
		part2 = MIMEText(html, 'html')
		msg.attach(part1)
		msg.attach(part2)
		mySmtp.sendmail(me, emailAddr, msg.as_string())
		mySmtp.quit()
	except:
		e = sys.exc_info()[1]
		print("Exception %s" %(e))

#
#
# it may looks biggy but all it does is summarising the logs and creates apropriate headings
# logs an entry and email the content to whom it suppose to :-)
# the logic is
# if run by a client without an email override then send email to the contact person for the client
# if email option was used then send email to the given address
# 
# if ran without any command line options the send email to the admin person only
#
# either way log an entry to the db for all clients with error in doi link

def processResultLists(client_id=None, admin_email=None):

	if len(resultList) == 0 and client_id:
		clientAppId = clientList[client_id][4]
		clientTitle = clientList[client_id][1]
		client_email = admin_email if admin_email else clientList[client_id][6]
		messageCont = 'Report Run: ' + datetime.datetime.now().strftime("%Y-%m-%d %H:%M") + '<br/>'
		messageCont = messageCont + '<br/>Broken Links Discovered: 0'
		messageCont = messageCont + '<br/>Client Name: ' + str(clientTitle)
		messageCont = messageCont + '<br/>Client App ID: ' + str(clientAppId)
		insertMessageLog(client_id, messageCont, 'SUCCESS')
		sendEmail(client_email, clientTitle, messageCont)
		print(messageCont)
	for owner_id, message in resultList.items():
		clientAppId = clientList[owner_id][4]
		clientTitle = clientList[owner_id][1]
		clientBrokenLinkCount = errorCount[owner_id]
		messageCont = 'Report Run: ' + datetime.datetime.now().strftime("%Y-%m-%d %H:%M") + '<br/>'
		messageCont = messageCont + '<br/>Broken Links Discovered: ' + str(clientBrokenLinkCount)
		messageCont = messageCont + '<br/>Client Name: ' + str(clientTitle)
		messageCont = messageCont + '<br/>Client App ID: ' + str(clientAppId)
		messageCont = messageCont + '<br/>DOIs with broken links:<br/>' + message
		insertMessageLog(owner_id, messageCont , 'FAILURE')
		if client_id:
			client_email = admin_email if admin_email else clientList[owner_id][6]
			sendEmail(client_email, clientTitle, messageCont)
			print(messageCont)
		else:
			admin_email = admin_email if admin_email else myconfig.adminEmailAddr 
			sendEmail(admin_email, clientTitle, messageCont)
	



#
#
# this was needed because some servers are giving relative path in the Location heading!!
# not valid... but most browsers handling it already
#

def constructAbsolutePath(scheme, host, port, path):
	if(path.find(scheme) == 0):
		return path	
	if(port):
		return scheme + "://" + host + ":" + port + path
	else:
		return scheme + "://" + host +  path

#
#
# check redirects up to 5 times
#
#

@asyncio.coroutine
def checkRedirect(url_str, creator, doi_id, counter, redirectCount=0):
	
	if redirectCount > 5:
		handleErrors(creator,'Too many redirects: DOI_ID: %s URL: %s' %(doi_id ,url_str), counter)
		return
	try:

		url = urllib.parse.urlsplit(url_str)
		if url.scheme.find('http') != 0:
			handleErrors(creator,'Not http: DOI_ID: %s URL: %s' %(doi_id ,url_str), counter)
			return		
		urlPath = url.path  if url.query == '' else url.path + "?" + url.query
		if url.scheme.find('https') == 0:
			port = url.port if url.port else 443
			reader, writer = yield from asyncio.open_connection(url.hostname, port, ssl=myconfig.context)
		else:
			port = url.port if url.port else 80
			reader, writer = yield from asyncio.open_connection(url.hostname, port)
		query =('HEAD ' + urlPath + ' HTTP/1.0\r\n'
	            'Host: {url.hostname}\r\n'
	            'User-agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6\r\n'
				'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5\r\n' 
				'Accept-Language: en-us,en;q=0.5\r\n'
				'Accept-Encoding: gzip,deflate\r\n'
				'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n'
				'Keep-Alive: 300\r\n'
	            '\r\n').format(url=url)
		writer.write(query.encode("utf-8"))
		while True:
			line = yield from reader.readline()
			if not line:
				break
			line = line.decode("utf-8").rstrip()
			if line:
				if(line.find('Content-Type') == 0):
					mType = line
				if(line.find('HTTP/1.') == 0):
					mStatus = line
				if(line.find('Location:') == 0) or (line.find('location:') == 0):
					location = line.split()[1]	 
		if mStatus:
			statusCode = int(mStatus.split()[1])
			if(statusCode > 399):
				handleErrors(creator,'4/500s: DOI_ID: %s URL: %s Status %s' %(doi_id, url_str, mStatus), counter)		
			elif statusCode == 301 or statusCode == 302:
				location = constructAbsolutePath(url.scheme, url.hostname, url.port, location)
				if(url_str != location):
					yield from checkRedirect(location, creator, doi_id, counter, redirectCount+1)
				else:
					handleErrors(creator,'REDIRECT URL SAME AS ORIGIN : DOI_ID: %s URL: %s' %(doi_id, url_str), counter)
			else:
				try:
					del testingArray[counter]
				except KeyError:
					pass
	except UnboundLocalError as e:
		handleErrors(creator,'Error: DOI_ID: %s URL: %s Exception %s' %(doi_id ,url_str, repr(e)), counter)
	except Exception as e:
		handleErrors(creator,'Error: DOI_ID: %s URL: %s Exception %s' %(doi_id ,url_str, repr(e)), counter)


#
# request the header for each resource and try to determin it is resolvable
# record a log entry if exception or 400/500 error occures
#
#


@asyncio.coroutine
def checkURLResource(r, counter):

	url_str = r[13].strip()
	creator = r[11];
	doi_id = r[0].strip();
	try:
		url = urllib.parse.urlsplit(url_str)
		if url.scheme.find('http') != 0:
			handleErrors(creator,'Not http: DOI_ID: %s URL: %s' %(doi_id, url_str), counter)
			return
		urlPath = url.path  if url.query == '' else url.path + "?" + url.query
		asyncio.sleep(0.3)
		if url.scheme.find('https') == 0:
			port = url.port if url.port else 443
			reader, writer = yield from asyncio.open_connection(url.hostname, port, ssl=myconfig.context)
		else:
			port = url.port if url.port else 80
			reader, writer = yield from asyncio.open_connection(url.hostname, port)
		query =('HEAD ' + urlPath + ' HTTP/1.0\r\n'
	            'Host: {url.hostname}\r\n'
	            'User-agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6\r\n'
				'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5\r\n' 
				'Accept-Language: en-us,en;q=0.5\r\n'
				'Accept-Encoding: gzip,deflate\r\n'
				'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n'
				'Keep-Alive: 300\r\n'
	            '\r\n').format(url=url)
		writer.write(query.encode("utf-8"))

		while True:
			line = yield from reader.readline()
			if not line:
				break
			line = line.decode("utf-8").rstrip()
			if line:
				if(line.find('Content-Type') == 0):
					mType = line
				if(line.find('HTTP/1.') == 0):
					mStatus = line
				if(line.find('Location:') == 0) or (line.find('location:') == 0): 
					location = line.split()[1]
		if mStatus:
			statusCode = int(mStatus.split()[1])
			if(statusCode > 399):
				handleErrors(creator,'4/500s: DOI_ID: %s URL: %s Status %s' %(doi_id, url_str, mStatus), counter)
			elif statusCode == 301 or statusCode == 302:
				location = constructAbsolutePath(url.scheme, url.hostname, url.port, location)
				if(url_str != location):
					yield from checkRedirect(location, creator, doi_id, counter)
				else:
					handleErrors(creator,'Error Redirect url same as original: DOI_ID: %s URL: %s' %(doi_id, url_str), counter)
			else:
				try:
					del testingArray[counter]
				except KeyError:
					pass
	except UnboundLocalError as e:
		handleErrors(creator,'Error DOI_ID: %s URL: %s exception %s' %(doi_id, url_str, repr(e)), counter)
	except Exception as e:
		handleErrors(creator,'Error DOI_ID: %s URL: %s exception %s' %(doi_id, url_str, repr(e)), counter)




#
#
# only production DOIs will be tested for either all or the given client_id
#
#
#

def getDOIlinksSL(client_id=None):

	cur = conn.cursor()
	if client_id:
		cur.execute("SELECT * FROM doi_objects where `client_id`="+ str(client_id) +" and `identifier_type`='DOI' and `status`!='REQUESTED' and `doi_id` LIKE '10.4%';")
	else:
		cur.execute("SELECT * FROM doi_objects where `identifier_type`='DOI' and `status`!='REQUESTED' and `doi_id` LIKE '10.4%';")
	for r in cur:
		doiList.append(r)
	cur.close()
	return doiList

#
#
# gather client information for generating personalised record for each test run
#
#

def getClientList(client_id=None):
	
	cur = conn.cursor()
	if client_id:
		cur.execute("SELECT * FROM doi_client where `client_id`="+ str(client_id) +";")
	else:
		cur.execute("SELECT * FROM doi_client;")
	for r in cur:
		clientList[r[0]] = r
	cur.close()
	

#
#
# chunk is reguired to avoid having too many open files
# also try not to DDOS our DOI clients
#

def runTest(client_id, admin_email):
	chunk = 20
	start = 0
	timeout = 20
	doiList = getDOIlinksSL(client_id)

	print("Number of URLs Tested: " + str(len(doiList)))
	socket.setdefaulttimeout(timeout)
	loop = asyncio.get_event_loop()
	asyncio.sleep(5)
	while len(doiList) > (int(start * chunk)):
		taskArray = []
		for num in range(start*chunk,((start+1)*chunk)-1):
			if(len(doiList) > num):
				testingArray[num] = {"url_str":doiList[num][13].strip(), "creator":doiList[num][11], "doi_id":doiList[num][0].strip()}
				taskArray.append(asyncio.async(checkURLResource(doiList[num],num)))
		try:
			loop.run_until_complete(asyncio.wait(taskArray, timeout=timeout))
			for k, v in testingArray.items():
				handleErrors(v['creator'],'Error DOI_ID: %s URL: %s CONNECTION TIMEOUT' %(v['doi_id'], v['url_str']), -1)
			testingArray.clear()
		except ValueError:
			print("num: %s range %s end %s" %(num, start*chunk, ((start+1)*chunk)-1))
		start = start + 1
	loop.close()

	processResultLists(client_id, admin_email)

def createConnection():
	try:
		return pymysql.connect(host=myconfig.db_host, user=myconfig.db_user, passwd=myconfig.db_passwd, db=myconfig.db)
	except:
		e = sys.exc_info()[1]
		print("Database Exception %s" %(e))
		sys.exit(0)
#
#
# main(argv)
# get command options run the test process
#

def main(argv):
	client_id = None
	admin_email = None
	try:
		opts, args = getopt.getopt(argv,"hc:e:",["client_id=","admin_email="])
	except getopt.GetoptError:
		print('doi_link_test.py -c <client_id> -e <admin_email>')
		sys.exit(2)
	for opt, arg in opts:
		if opt == '-h':
			print('doi_link_test.py -c <client_id> -e <admin_email>')
			sys.exit()
		elif opt in ("-c", "--client_id"):
			client_id = int(arg)
		elif opt in ("-e", "--admin_email"):
			admin_email = arg
	getClientList(client_id)
	runTest(client_id, admin_email)


resultList = {}
doiList = []
clientList = {}
errorCount = {}
testingArray = {}
conn = createConnection()

if __name__ == "__main__":
	try:
		main(sys.argv[1:])
		conn.close()
	except:
		e = sys.exc_info()[1]
		print("Something Bad Has Happened %s" %(e))
		sys.exit(0)


