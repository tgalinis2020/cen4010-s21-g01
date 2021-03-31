<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL;
use ThePetPark\Http;
use ThePetPark\Services;
use ThePetPark\Middleware;
use ThePetPark\Library\Graph;

use function DI\create;
use function DI\factory;
use function DI\get;

return [

    Middleware\Session::class => create(Middleware\Session::class)
        ->constructor(get(Services\JWT\Decoder::class)),

    Http\UploadFile::class => factory(function (ContainerInterface $c) {
        return new Http\UploadFile($c->get('settings')['uploadDirectory']);
    }),

    Http\Session\Resolve::class => create(Http\Session\Resolve::class),
    
    Http\Session\Create::class => create(Http\Session\Create::class)
        ->constructor(get(DBAL\Connection::class), get(Services\JWT\Encoder::class)),
    
    Http\Session\Delete::class => create(Http\Session\Delete::class),

    Http\Passwords\Set::class => create(Http\Passwords\Set::class)
        ->constructor(get(DBAL\Connection::class)),

    Http\Passwords\Update::class => create(Http\Passwords\Update::class)
        ->constructor(get(DBAL\Connection::class)),
    
    Http\Actions\Resolve::class => factory(function (ContainerInterface $c) {
        return new Http\Actions\Resolve(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class)
        );
    }),

    Http\Actions\Add::class => factory(function (ContainerInterface $c) {
        return new Http\Actions\Add(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class),
            $c->get('settings')['baseUrl']
        );
    }),

    Http\Actions\Update::class => factory(function (ContainerInterface $c) {
        return new Http\Actions\Update(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class)
        );
    }),

    Http\Actions\Remove::class => factory(function (ContainerInterface $c) {
        return new Http\Actions\Remove(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class)
        );
    }),

    Http\Actions\Relationships\Add::class => factory(function (ContainerInterface $c) {
        return new Http\Actions\Relationships\Add(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class),
            $c->get('settings')['baseUrl']
        );
    }),

    Http\Actions\Relationships\Remove::class => factory(function (ContainerInterface $c) {
        return new Http\Actions\Relationships\Remove(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class),
            $c->get('settings')['baseUrl'] ?? 'http://localhost'
        );
    }),

    Http\Actions\Relationships\Update::class => factory(function (ContainerInterface $c) {
        return new Http\Actions\Relationships\Update(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class)
        );
    }),
];
