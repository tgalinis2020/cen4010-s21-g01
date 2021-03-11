<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use ThePetPark\Http;

use function DI\factory;

/**
 * Controllers unrelated to API resources are bootstrapped here.
 */
return [

    Http\HelloWorld::class => factory(function (ContainerInterface $c) {
        return new Http\HelloWorld();
    }),

    Http\Search::class => factory(function (ContainerInterface $c) {
        return new Http\Search($c->get(Connection::class));
    }),

    Http\Auth\EchoSession::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['firebase']['php-jwt'];

        return new Http\Auth\EchoSession(
            $settings['secret_key'],
            $settings['allowed_algs']
        );
    }),

    Http\Auth\Login::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['firebase']['php-jwt'];

        return new Http\Auth\Login(
            $settings['alg'],
            $settings['secret_key'],
            $c->get(Connection::class)
        );
    }),

    Http\Auth\Logout::class => factory(function (ContainerInterface $c) {
        return new Http\Auth\Logout();
    }),

    Http\Auth\Register::class => factory(function (ContainerInterface $c) {
        return new Http\Auth\Register($c->get(Conntection::class));
    }

];