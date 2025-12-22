<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'chatwoot' => [
    'base_url'   => env('CHATWOOT_BASE_URL'),
    'account_id' => env('CHATWOOT_ACCOUNT_ID'),
    'api_token'  => env('CHATWOOT_API_TOKEN'),
    ],

    'openai' => [
    'key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'max_tokens' => env('OPENAI_MAX_TOKENS', 250),
    'temperature' => env('OPENAI_TEMPERATURE', 0.2),
    ],

    'perplexity' => [
    'key'   => env('PERPLEXITY_API_KEY'),
    'model' => env('PERPLEXITY_MODEL', 'sonar-pro'),
    'url'   => env('PERPLEXITY_API_URL', 'https://api.perplexity.ai'),
    ],




];
