<?php

declare(strict_types=1);

use ThePetPark\Library\Graph\Drivers\Doctrine\Features;

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
        'routerCacheFile' => dirname(__DIR__) . '/var/cache/routes.php',
        'uploadDirectory' => dirname(__DIR__, 4) . '/public_html/uploads',

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
            'connection' =>  function () {
                // Use MySQL config file to initialize the database connection.
                $conf = parse_ini_file(dirname(__DIR__, 4) . '/.my.cnf');

                return [
                    'driver'   => 'pdo_mysql',
                    'host'     => $conf['host'],
                    'port'     => $conf['port'],
                    'dbname'   => $conf['database'],
                    'charset'  => $conf['charset'],
                    'user'     => $conf['user'],
                    'password' => $conf['pass'],
                ];
            },
        ],

        'graph' => [
            'definitions' => dirname(__DIR__) . '/var/cache/schemas.php',
            'baseUrl' => 'https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php',
            'driver' => [
                'settings' => [
                    'defaultPageSize' => 15,
                ],

                'features' => [
                    Features\Pagination\CursorStrategy::class,
                    Features\Pagination\PageStrategy::class,
                    Features\Pagination\OffsetLimitStrategy::class,
                    Features\Filters::class,
                    Features\Sorting::class,
                ],
            ],
        ],
    ],
];