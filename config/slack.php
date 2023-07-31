<?php

return [
    'web_hook_url' => env('SLACK_WEBHOOK_URL', ''),
    'channel_id' => env('SLACK_CHANNEL_ID', ''),
    'log_level' => env('SLACK_LOG_LEVEL', 'ERROR')
];