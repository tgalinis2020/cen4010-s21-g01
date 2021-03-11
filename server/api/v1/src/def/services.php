<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL;
use Firebase\JWT\JWT;

use function DI\factory;

/**
 * Any dependencies that are not provided by Slim (other than Controllers)
 * should be listed here.
 */
return [

    DBAL\Connection::class => factory(function (ContainerInterface $c) {
        return DBAL\DriverManager::getConnection(
            $c->get('settings')['doctrine']['connection']
        );
    }),

    'jwt_encoder' => factory(function (ContainerInterface $c) {
        $settings = $c->get('firebase')['php-jwt'];

        $secret = $settings['secret_key'];
        $alg = $settings['alg'];

        return function ($payload) use ($secret, $alg) {
            return JWT::encode($payload, $secret, $alg);
        };
    }),

    'jwt_decoder' => factory(function (ContainerInterface $c) {
        $settings = $c->get('firebase')['php-jwt'];

        $secret = $settings['secret_key'];
        $allowed = $settings['allowed_algs'];

        return function ($token) use ($secret, $allowed) {
            JWT::decode($token, $secret, $allowed);
        };
    }),

];
