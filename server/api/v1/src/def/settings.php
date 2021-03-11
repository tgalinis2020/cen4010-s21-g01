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
                // tokens on this app's behalf.
                'secret_key' => (require __DIR__ . '/../../../../../secret.php'),
                'alg' => 'HS226',
                'allowed_algs' => ['HS256', 'HS512'],
            ],
        ],

        'doctrine' => [
            'connection' => (function () {
                list($user, $passwd) = (require __DIR__ . '/../../../../../connection.php');

                // Kind of a silly way to leave DB credentials out of
                // version control, but it's necessary since it is not
                // possible to set environment variables and/or read
                // files outside of ~/public_html in FAU's LAMP server.
                //
                // In the project's root directory, a file called "credentials.php"
                // must exist. The contents of the file should look something
                // like this:
                //
                // <?php return ['mysql-account-name', 'super-secret-password'];

                return [
                    'driver' => 'pdo_mysql',
                    'host' => '127.0.0.1',
                    'port' => 3306,
                    'dbname' => 'thepetpark',
                    'charset' => 'utf-8',
                    'user' => $user,
                    'password' => $passwd,
                ];
            })()
        ],
    ],
];