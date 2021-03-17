<?php

declare(strict_types=1);

/**
 * Slim application settings along with configuation for third-party
 * dependnecies.
 */
return (function () {
    
    // The secret is a series of random bytes that should make it
    // nearly impossible for malicious actors to forge their own
    // tokens and pretend to be another user.
    $secretKey = file_get_contents(__DIR__ . '/../../../../../secret.txt');

    // Use MySQL config file to initialize the database connection.
    $conf = parse_ini_file(__DIR__ . '/../../../../../.my.cnf');

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
                    'secret_key' => $secretKey,
                    'algorithms' => ['HS256', 'HS512'],
                    'selected_algorithm' => 0,
                ],
            ],

            'doctrine' => [
                'connection' => [
                    'driver'   => 'pdo_mysql',
                    'host'     => $conf['host'],
                    'port'     => $conf['port'],
                    'dbname'   => $conf['database'],
                    'charset'  => 'utf8',
                    'user'     => $conf['user'],
                    'password' => $conf['pass'],
                ]
            ],

            'graph' => [
                'cache' => __DIR__ . '/../..//var/cache/thepetpark.cache',
            ],
        ],
    ];
})();