#!/bin/bash

#htaccess
rm -rf .htaccess
cp htaccess.sample .htaccess
rm -rf global_config.php
cp global_config.sample global_config.php

function ask_replace {
	#file question default_value replace
    printf "$2 ($3): "
	read v
	base=${v:-$3}
	#echo "Setting Application Base ($1) to: $base"
	sed -i '' s/"$4"/$(echo $base | awk '{gsub(".", "\\\\&");print}')/g $1
} 

ask_replace .htaccess "Application Path" "/" "@@app_base"
ask_replace global_config.php "Base URL" "http://researchdata.ands.org.au/" "@@base_url"
ask_replace global_config.php "SOLR URL" "http://researchdata.ands.org.au:8080/solr/" "@@solr_url"
ask_replace global_config.php "Sissvoc URL" "http://researchdata.ands.org.au:8080/sissvoc/" "@@sissvoc_url"
ask_replace global_config.php "Harvester URL" "http://researchdata.ands.org.au:8080/harvester/" "@@harvester_url"
ask_replace global_config.php "PIDs URL" "http://researchdata.ands.org.au:8080/pids/" "@@pids_url"
ask_replace global_config.php "PIDs APP ID" "" "@@pids_app_id"