<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Http;
use ThePetPark\Services;
use Doctrine\DBAL\Connection;

use function DI\factory;

return [

    Http\HelloWorld::class => factory(function (ContainerInterface $c) {
        return new Http\HelloWorld();
    }),

    Http\UploadFile::class => factory(function (ContainerInterface $c) {
        return new Http\UploadFile();
    }),

    Http\Session\Show::class => factory(function (ContainerInterface $c) {
        return new Http\Session\Show();
    }),

    Http\Session\Create::class => factory(function (ContainerInterface $c) {
        return new Http\Session\Create(
            $c->get(Connection::class),
            $c->get(Services\JWT\Encoder::class)
        );
    }),

    Http\Session\Destroy::class => factory(function (ContainerInterface $c) {
        return new Http\Session\Destroy();
    }),

];