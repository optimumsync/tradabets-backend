<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send any email
    | message by your application. You may set this to any of the mailers
    | defined in your "mailers" array below.
    |
    */
    

    'default' => env('MAIL_MAILER', 'smtp'), // Keep this or set to 'brevo_smtp' if you want a dedicated name

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail drivers to send messages, and may
    | be used with the Mail facade as well as the Mail::send function.
    |
    */

    'mailers' => [
        'smtp' => [ // This will be your default SMTP mailer, configured for Brevo
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp-relay.brevo.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'), // This will be your Brevo SMTP username
            'password' => env('MAIL_PASSWORD'), // This will be your Brevo SMTP password (API Key)
            'timeout' => null,
            'auth_mode' => null, // Brevo works fine with default auth
        ],

        // You can keep other mailers if you use them, or remove them if not
        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all e-mails sent by your application to be sent from
    | the same address. Here, you may specify a name and address that is
    | used globally for all e-mails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Tradabets'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    |
    | If you are using Markdown based email rendering, you may configure your
    | theme and component paths here, allowing you to customize the design
    | of the emails. Or, you may simply stick with the Laravel defaults!
    |
    */

    'markdown' => [
        'theme' => 'default',

        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stream Context Options
    |--------------------------------------------------------------------------
    |
    | This section allows you to configure stream context options for SMTP
    | connections. This can be useful for debugging SSL/TLS issues.
    | Be cautious with 'allow_self_signed' and 'verify_peer' in production.
    |
    */

    'stream' => [
        'ssl' => [
            'allow_self_signed' => env('MAIL_ALLOW_SELF_SIGNED', false),
            'verify_peer' => env('MAIL_VERIFY_PEER', true),
            'verify_peer_name' => env('MAIL_VERIFY_PEER_NAME', true),
        ],
    ]

];