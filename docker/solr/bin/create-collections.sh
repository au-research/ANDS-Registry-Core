#!/bin/sh

/opt/solr/bin/solr zk upconfig -d /confs/conf/portal-solr8-config -n portal -z zookeeper:2181
/opt/solr/bin/solr create -c portal -n portal

/opt/solr/bin/solr zk upconfig -d /confs/conf/concepts-solr8-config -n concepts -z zookeeper:2181
/opt/solr/bin/solr create -c concepts -n concepts

/opt/solr/bin/solr zk upconfig -d /confs/conf/relationships-solr8-config -n relationships -z zookeeper:2181
/opt/solr/bin/solr create -c relationships -n relationships