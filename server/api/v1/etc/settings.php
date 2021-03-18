<?php

declare(strict_types=1);

use ThePetPark\Library\Graph\App as Graph;

/**
 * Slim application settings along with configuation for third-party
 * dependnecies.
 */
return (function () {

    // Use MySQL config file to initialize the database connection.
    $conf = parse_ini_file(__DIR__ . '/../../../../.my.cnf');

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
                    // tokens and masquerade as another user.
                    'secret_key' => __DIR__ . '/secret.key',
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
                    'charset'  => $conf['charset'],
                    'user'     => $conf['user'],
                    'password' => $conf['pass'],
                ]
            ],

            'graph' => [
                'definitions' => __DIR__ . '/../var/cache/graph.cache',
                'pagination' => [
                    'maxPageSize' => 15,
                ],
                'strategies' => [
                    Strategies\Pagination\Cursor::class,
                    Strategies\Filtering\Granular::class,
                    Strategies\Sorting\Simple::class,
                ]
            ],
        ],
    ];
})();