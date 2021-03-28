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

    Graph\Adapters\Slim\Adapter::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['graph'];

        $driver = new Graph\Drivers\Doctrine\Driver(
            $c->get(DBAL\Connection::class),
            $settings['driver']
        );

        $graph = new Graph\App($settings, $driver, $c->get('response'), $c);

        return new Graph\Adapters\Slim\Adapter($graph);
    }),

    Middleware\Session::class => create(Middleware\Session::class)
        ->constructor(get(Services\JWT\Decoder::class)),

    Http\UploadFile::class => factory(function (ContainerInterface $c) {
        return new Http\UploadFile(
            $c->get('settings')['uploadDirectory']
        );
    }),

    Http\Session\Resolve::class => create(Http\Session\Resolve::class),

    Http\Session\Create::class => create(Http\Session\Create::class)
        ->constructor(get(Connection::class), get(Services\JWT\Encoder::class)),

    Http\Session\Delete::class => create(Http\Session\Delete::class),

    Http\Actions\Users\Create::class => create(Http\Actions\Users\Create::class)
        ->constructor(get(Connection::class)),

    Http\Actions\Users\Update::class => create(Http\Actions\Users\Update::class)
        ->constructor(get(Connection::class)),

    Http\Actions\Posts\Create::class => create(Http\Actions\Posts\Create::class)
        ->constructor(get(Connection::class)),

    Http\Actions\Posts\Update::class => create(Http\Actions\Posts\Update::class)
        ->constructor(get(Connection::class)),

    Http\Actions\Comments\Create::class => create(Http\Actions\Comments\Create::class)
        ->constructor(get(Connection::class)),

    Http\Actions\Comments\Update::class => create(Http\Actions\Comments\Update::class)
        ->constructor(get(Connection::class)),

    Http\Actions\Pets\Create::class => create(Http\Actions\Pets\Create::class)
        ->constructor(get(Connection::class)),

    Http\Actions\Pets\Update::class => create(Http\Actions\Pets\Update::class)
        ->constructor(get(Connection::class)),

];
