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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'telegram' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'chats' => [-1002092160461],
    ],
    'flix' => [
        'url' => env('FLIX_URL'),
        'token' => env('FLIX_TOKEN'),
    ],
    'alert_telegram' => [
        'token' => env('ALERT_TELEGRAM_BOT_TOKEN'),
        'chats' => array_filter(array_map('trim', explode(',', env('ALERT_TELEGRAM_CHAT_IDS', '')))),
    ],
    'ssh_tunnel' => [
        'host' => env('SSH_TUNNEL_HOST'),
        'port' => env('SSH_TUNNEL_PORT', '22'),
        'user' => env('SSH_TUNNEL_USER'),
        'key_path' => env('SSH_TUNNEL_KEY_PATH'),
        'password' => env('SSH_TUNNEL_PASSWORD'),
        'remote_db_host' => env('SSH_TUNNEL_REMOTE_DB_HOST', '127.0.0.1'),
        'remote_db_port' => env('SSH_TUNNEL_REMOTE_DB_PORT', '3306'),
        'local_port' => env('SSH_TUNNEL_LOCAL_PORT', '13306'),
    ],
];
