<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Ligação à base de dados
        'db' => [
                // Eloquent configuration
                'driver'    => 'mysql',
                'host'      => 'localhost',
                'database'  => 'arimba_bt',
                'username'  => 'bp5am',
                'password'  => 'bp5ampass',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
        ]
    ],
];
