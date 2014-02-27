<?php

define('NATIVE_HARVEST_FORMAT_TYPE','nativeHarvestData');

define('HARVEST_ERROR','error');
define('HARVEST_WARNING','warning');
define('HARVEST_INFO','info');
define('HARVEST_TEST_MODE','TEST');
define('HARVEST_COMPLETE','TRUE');

define('IMPORT_INFO','info');

define('EXTRIF_SCHEME','extRif');

define('DRAFT_RECORD_SLUG','draft_record_');

define('SUCCESS','SUCCESS');
define('FAILURE','FAILURE');

define('PRIMARY_RELATIONSHIP','PRIMARY');

/* Search boost scores */
// Amount to boost for contributor pages
define('SEARCH_BOOST_CONTRIBUTOR_PAGE', 3);

// Exponential per relation (1.1**6)
define('SEARCH_BOOST_PER_RELATION_EXP', 1.1);

// Max to allocate based on relations/connectedness
define('SEARCH_BOOST_RELATION_MAX', 4);