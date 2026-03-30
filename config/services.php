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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.1-pro-preview'),
        'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
    ],

    'supertone' => [
        'api_key' => env('SUPERTONE_API_KEY'),
        'voice_id' => env('SUPERTONE_VOICE_ID'),
        'model' => env('SUPERTONE_MODEL', 'sona_speech_1'),
        'base_url' => env('SUPERTONE_BASE_URL', 'https://supertoneapi.com'),
        'default_language' => env('SUPERTONE_DEFAULT_LANGUAGE', 'ko'),
        'default_style' => env('SUPERTONE_DEFAULT_STYLE'),
        'storage_disk' => env('SUPERTONE_STORAGE_DISK', 's3'),
        'storage_prefix' => env('SUPERTONE_STORAGE_PREFIX', 'audio/supertone-tts'),
        'use_temporary_url' => (bool) env('SUPERTONE_USE_TEMPORARY_URL', true),
        'temporary_url_minutes' => (int) env('SUPERTONE_TEMPORARY_URL_MINUTES', 60),
    ],

];
