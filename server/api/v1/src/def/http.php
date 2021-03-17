<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Http;
use ThePetPark\Services;
use Doctrine\DBAL\Connection;

use function DI\factory;

/**
 * Controllers unrelated to API resources are bootstrapped here.
 */
return [

    Http\HelloWorld::class => factory(function (ContainerInterface $c) {
        return new Http\HelloWorld();
    }),

    /*
    // Deprecated. Make requests to /posts, /users, and /pets instead.
    // Parse special tokens on client-side applications.
    Http\Search::class => factory(function (ContainerInterface $c) {
        return new Http\Search(
            $c->get(UserRepository::class),
            $c->get(PetRepository::class),
            $c->get(PostRepository::class)
        );
    }),
    */

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