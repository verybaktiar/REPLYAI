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
    'timeout' => (int) env('PERPLEXITY_TIMEOUT', 30),
    ],
    'instagram' => [
        'app_id' => env('INSTAGRAM_APP_ID'),
        'app_secret' => env('INSTAGRAM_APP_SECRET'),
        'redirect_uri' => env('INSTAGRAM_REDIRECT_URI', '/instagram/callback'),
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN'), // Legacy global token
        'webhook_verify_token' => env('WEBHOOK_VERIFY_TOKEN'),
        'meta_app_secret' => env('META_APP_SECRET'),
        'instagram_user_id' => env('INSTAGRAM_USER_ID'), // Legacy global ID
    ],

    'whatsapp' => [
        'url' => env('WA_SERVICE_URL', 'http://127.0.0.1:3001'),
        'webhook_key' => env('WA_SERVICE_KEY', 'replyai-wa-secret'),
    ],

    'megallm' => [
        'enabled' => env('MEGALLM_ENABLED', true),
        'key' => env('MEGALLM_API_KEY'),
        'url' => env('MEGALLM_BASE_URL', 'https://ai.megallm.io/v1'),
        'model' => env('MEGALLM_MODEL', 'moonshotai/kimi-k2-instruct-0905'),
        'timeout' => (int) env('MEGALLM_TIMEOUT', 90),
        'retries' => (int) env('MEGALLM_RETRIES', 2),
        'retry_sleep_ms' => (int) env('MEGALLM_RETRY_SLEEP_MS', 700),
        'fallback_models' => env('MEGALLM_FALLBACK_MODELS', 'deepseek-ai/deepseek-v3.1,gemini-2.5-flash'),
    ],

    'sumopod' => [
        'enabled' => env('SUMOPOD_ENABLED', true),
        'key' => env('SUMOPOD_API_KEY'),
        'url' => env('SUMOPOD_BASE_URL', 'https://ai.sumopod.com/v1'),
        'model' => env('SUMOPOD_MODEL', 'kimi-k2-5-260127-free'),
        'timeout' => (int) env('SUMOPOD_TIMEOUT', 90),
        'retries' => (int) env('SUMOPOD_RETRIES', 2),
        'retry_sleep_ms' => (int) env('SUMOPOD_RETRY_SLEEP_MS', 700),
        'fallback_models' => env('SUMOPOD_FALLBACK_MODELS', 'seed-2-0-mini-free,whisper-1'),
    ],

    'ai_failover' => [
        'enabled' => env('AI_FAILOVER_ENABLED', true),
        'primary' => env('AI_PRIMARY_PROVIDER', 'megallm'),
        'secondary' => env('AI_SECONDARY_PROVIDER', 'sumopod'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Configuration
    |--------------------------------------------------------------------------
    |
    | Pilihan provider: 'hcaptcha', 'recaptcha', 'turnstile'
    | - hCaptcha: Gratis 1 juta/bulan, privacy-focused (recommended)
    | - reCAPTCHA v3: Gratis 1 juta/bulan, Google
    | - Cloudflare Turnstile: Gratis unlimited
    |
    */
    'captcha' => [
        'enabled' => env('CAPTCHA_ENABLED', false),
        'provider' => env('CAPTCHA_PROVIDER', 'hcaptcha'), // hcaptcha, recaptcha, turnstile
        'site_key' => env('CAPTCHA_SITE_KEY'),
        'secret' => env('CAPTCHA_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Suggestions Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI-powered reply suggestions feature.
    | Supports: 'openai', 'claude', 'megallm'
    |
    */
    'ai_suggestions' => [
        'enabled' => env('AI_SUGGESTIONS_ENABLED', true),
        'provider' => env('AI_SUGGESTIONS_PROVIDER', 'megallm'), // openai, claude, megallm
        'model' => env('AI_SUGGESTIONS_MODEL', 'mistral-large-3-675b-instruct-2512'),
        'cache_duration' => (int) env('AI_SUGGESTIONS_CACHE_DURATION', 300), // 5 minutes
        'max_context_messages' => (int) env('AI_SUGGESTIONS_MAX_CONTEXT', 10),
        'fallback_enabled' => true,
    ],

    'claude' => [
        'key' => env('CLAUDE_API_KEY'),
        'model' => env('CLAUDE_MODEL', 'claude-3-haiku-20240307'),
    ],

];
