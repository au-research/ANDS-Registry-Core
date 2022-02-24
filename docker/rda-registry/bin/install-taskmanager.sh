#!/bin/bash

cd /opt/apps/taskmanager/current
rm -rf venv && rm -rf __pycache__
/usr/bin/python3 -m venv venv && venv/bin/pip3 install --upgrade pip && venv/bin/pip3 install -r requirements.txt