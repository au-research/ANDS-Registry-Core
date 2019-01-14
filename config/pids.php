<?php

return [
    'identifier_suffix' => env('PIDS_IDENTIFIER_SUFFIX', 'https://researchdata.ands.org.au'),
    'server_base_url'=>  env('PIDS_SERVER_BASE_URL', "https://handle.ands.org.au/pids/"),
    'server_app_id'=> env('PIDS_SERVER_APP_ID', "b50d89cbeeabb59c9679271b06fd5ba44234feb"),
    'url_prefix'=> env('PIDS_URL_PREFIX', "https://handle.ands.org.au/pids")
];
