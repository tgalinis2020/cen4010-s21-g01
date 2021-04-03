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
        //'routerCacheFile' => dirname(__DIR__) . '/var/cache/routes.php',
        'routerCacheFile' => false,
        'uploadDirectory' => [
            'root' => dirname(__DIR__, 4) . '/public_html', // root location w.r.t filesystem
            'base' => '/~cen4010_s21_g01', // root directory w.r.t URL
            'endpoint' => '/uploads', // where to store uploads
        ],

        // API settings
        'defaultPageSize' => 12,
        'definitions' => dirname(__DIR__) . '/var/cache/schemas.php',
        'baseUrl' => 'https://lamp.cse.fau.edu/~cen4010_s21_g01/api-v1.php',

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
                    'charset'  => 'utf8',
                    'user'     => $conf['user'],
                    'password' => $conf['pass'],
                ];
            },
        ],
    ],
];