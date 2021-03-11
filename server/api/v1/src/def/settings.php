<?php

declare(strict_types=1);

/**
 * Slim application settings along with configuation for third-party
 * dependnecies.
 */
return [
    'settings' => [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'append',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true,
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,

        'firebase' => [
            'php-jwt' => [
                // The secret is a series of random bytes that should make it
                // nearly impossible for malicious actors to forge their own
                // tokens and pretent to be another user.
                'secret_key' => file_get_contents(__DIR__ . '/../../../../../secret.txt'),
                'algorithms' => ['HS256', 'HS512'],
                'selected_algorithm' => 0,
            ],
        ],

        'doctrine' => [
            'connection' => (function () {
                $config = parse_ini_file(__DIR__ . '/../../../../../.my.cnf');

                return [
                    'driver' => 'pdo_mysql',
                    'host' => '127.0.0.1',
                    'port' => 3306,
                    'dbname' => $config['database'],
                    'charset' => 'utf-8',
                    'user' => $config['user'],
                    'password' => $config['pass'],
                ];
            })()
        ],
    ],
];