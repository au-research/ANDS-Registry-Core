#!/bin/sh

mkdir -p /var/data/harvester && setfacl -R -d -m apache:rwx /var/data/harvester && setfacl -R -m u:apache:rwx /var/data/harvester
mkdir -p /var/log/harvester && mkdir -p /var/data/harvester
mkdir -p /var/log/taskmanager && mkdir -p /var/data/taskmanager
mkdir -p /var/data/registry && setfacl -R -d -m apache:rwx /var/data/registry && setfacl -R -m u:apache:rwx /var/data/registry
mkdir -p /var/log/registry && setfacl -R -d -m apache:rwx /var/log/registry && setfacl -R -m u:apache:rwx /var/log/registry