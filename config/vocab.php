<?php

$sissvocUrl = env('SISSVOC_URL', "https://vocabs.ardc.edu.au/repository/api/lda/");

return [
    'sissvoc_url' => $sissvocUrl,
    'vocab_resolving_services' =>[
        'anzsrc-seo' => ['resolvingService' => $sissvocUrl.'anzsrc-seo/', 'uriprefix' => 'http://purl.org/au-research/vocabulary/anzsrc-seo/2008/'],
        'anzsrc-for' => ['resolvingService' => $sissvocUrl.'anzsrc-for/', 'uriprefix' => 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/'],
        'anzsrc-for-2020' => ['resolvingService' => $sissvocUrl.'anzsrc-2020-for/', 'uriprefix' => 'https://linked.data.gov.au/def/anzsrc-for/2020/'],
        'anzsrc-seo-2020' => ['resolvingService' => $sissvocUrl.'anzsrc-2020-seo/', 'uriprefix' => 'https://linked.data.gov.au/def/anzsrc-seo/2020/'],
        'GCMD' => ['resolvingService' => $sissvocUrl.'GCMD-sci/', 'uriprefix' => 'http://gcmdservices.gsfc.nasa.gov/kms/concept/']
    ]
];

