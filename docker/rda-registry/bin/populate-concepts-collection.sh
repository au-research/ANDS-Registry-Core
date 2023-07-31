#!/bin/sh

cd /opt/solr-import-export-json
/usr/bin/python3 -m venv venv && venv/bin/pip3 install --upgrade pip && venv/bin/pip3 install -r requirements.txt

export LC_ALL=en_US.utf-8
export LANG=en_US.utf-8

CONCEPTS_EXPORT=/tmp/concepts.json
if test -f "$CONCEPTS_EXPORT"; then
    echo "$CONCEPTS_EXPORT exists. Importing this file"
else 
    echo "$CONCEPTS_EXPORT does not exist. Attempt to obtain"
    venv/bin/python -m src.solr_export -s http://130.56.60.126:8983/solr/concepts -f ${CONCEPTS_EXPORT} -d
fi

venv/bin/python -m src.solr_import -s http://solr:8983/solr/concepts -f ${CONCEPTS_EXPORT} -d