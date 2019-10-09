<?php

$sysscovUrl = env('SISSVOC_URL', "https://vocabs.ands.org.au/repository/api/lda/");

return [
    'sissvoc_url' => $sysscovUrl,
    'vocab_resolving_services' =>[
        'anzsrc-seo' => ['resolvingService' => $sysscovUrl.'anzsrc-seo/', 'uriprefix' => 'http://purl.org/au-research/vocabulary/anzsrc-seo/2008/'],
        'anzsrc-for' => ['resolvingService' => $sysscovUrl.'anzsrc-for/', 'uriprefix' => 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/'],
        'GCMD' => ['resolvingService' => $sysscovUrl.'gcmd-sci/', 'uriprefix' => 'http://gcmdservices.gsfc.nasa.gov/kms/concept/']
    ]
];

