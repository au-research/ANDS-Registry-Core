<?php

return [
    'base_url' => baseUrl('/auth/oauth/'),

    "providers" => [
        // openid providers
        "OpenID" => [
            "enabled" => false
        ],

        "Yahoo" => [
            "enabled" => false,
            "keys"    => ["id" => "", "secret" => ""],
        ],

        "Google" => [
            "enabled" => env("OAUTH_GOOGLE_ID") ? true : false,
            "keys"    => [
                "id" => env("OAUTH_GOOGLE_ID", ""),
                "secret" => env("OAUTH_GOOGLE_SECRET", "")
            ],
        ],

        "Facebook" => [
            "enabled" => env("OAUTH_FACEBOOK_ID") ? true : false,
            "keys"    => [
                "id" => env("OAUTH_FACEBOOK_ID", ""),
                "secret" => env("OAUTH_FACEBOOK_SECRET", "")
            ],
        ],

        "Twitter" => [
            "enabled" => env("OAUTH_TWITTER_KEY") ? true : false,
            "keys" => [
                "key" => env("OAUTH_TWITTER_KEY"),
                "secret" => env("OAUTH_TWITTER_SECRET", "")
            ]
        ],

        "LinkedIn" => [
            "enabled" => false,
            "keys"    => ["key" => "", "secret" => ""]
        ],

    ]
];