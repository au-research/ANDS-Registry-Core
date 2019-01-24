<?php

return [

'vocab_config' => [
        // This must resolve to the Vocabs toolkit. Use web server
        // configuration to proxy, if necessary.
        'toolkit_url' => 'http://localhost/repository/api/toolkit/',
        // Location of the Solr search index core which you have installed
        'solr_url' => 'http://devl.ands.org.au:8983/solr/vocabs/',
        // This can point to localhost, or to another installation
        // of the ANDS Registry.
        'auth_url' => 'https://devl.ands.org.au/richard/vocabs/registry/auth/',
        // This should be a subdirectory within the Vocab Toolkit's
        // Toolkit.tempPath setting.
        'upload_path' => '/var/vocab-files/toolkit-data/temp/uploads/',
        // This should match the Vocab Toolkit's Toolkit.storagePath
        // setting.
        'repository_path' => '/var/vocab-files/toolkit-data/vocabs/'
    ],
    'sissvoc_url' => "http://vocabs.ands.org.au/repository/api/lda/",
    'vocab_resolving_services' =>[
    'anzsrc-seo' => ['resolvingService' => 'http://demo.ands.org.au/repository/api/lda/anzsrc-seo/', 'uriprefix' => 'http://purl.org/au-research/vocabulary/anzsrc-seo/2008/'],
    'anzsrc-for' => ['resolvingService' => 'http://demo.ands.org.au/repository/api/lda/anzsrc-for/', 'uriprefix' => 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/']
    ]
];

