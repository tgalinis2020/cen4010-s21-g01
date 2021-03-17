<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL;
use ThePetPark\Http;
use ThePetPark\Services;
use ThePetPark\Middleware;
use ThePetPark\Library\Graph\Graph;

use function DI\factory;

return [

    DBAL\Connection::class => factory(function (ContainerInterface $c) {
        return DBAL\DriverManager::getConnection(
            $c->get('settings')['doctrine']['connection']
        );
    }),

    Services\JWT\Encoder::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['firebase']['php-jwt'];

        return new Services\JWT\Encoder(
            $settings['secret_key'],
            $settings['algorithms'][$settings['selected_algorithm']]
        );
    }),

    Services\JWT\Decoder::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['firebase']['php-jwt'];

        return new Services\JWT\Decoder(
            $settings['secret_key'],
            $settings['algorithms']
        );
    }),

    Graph::class => factory(function (ContainerInterface $c) {
        return new Graph(
            $c->get(DBAL\Connection::class),
            $c->get('settings')['graph']
        );
    }),

    Middleware\Session::class => factory(function (ContainerInterface $c) {
        return new Middleware\Session(
            $c->get(Services\JWT\Decoder::class)
        );
    }),

    Http\UploadFile::class => factory(function (ContainerInterface $c) {
        return new Http\UploadFile();
    }),

    Http\Session\Resolve::class => factory(function (ContainerInterface $c) {
        return new Http\Session\Resolve();
    }),

    Http\Session\Create::class => factory(function (ContainerInterface $c) {
        return new Http\Session\Create(
            $c->get(Connection::class),
            $c->get(Services\JWT\Encoder::class)
        );
    }),

    Http\Session\Delete::class => factory(function (ContainerInterface $c) {
        return new Http\Session\Delete();
    }),

];
