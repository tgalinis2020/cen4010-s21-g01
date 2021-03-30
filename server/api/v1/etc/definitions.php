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

    Graph\Schema\Container::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['graph'];
        $schemas = [];

        foreach ((require $settings['definitions']) as $definition) { 
            $schema = Graph\Schema::fromArray($definition);
            $schemas[$schema->getType()] = $schema;
        }

        return new Graph\Schema\Container($schemas);
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
        ->constructor(get(DBAL\Connection::class), get(Services\JWT\Encoder::class)),
    
    Http\Session\Delete::class => create(Http\Session\Delete::class),

    Http\Passwords\Set::class => create(Http\Passwords\Set::class)
        ->constructor(get(DBAL\Connection::class)),

    Http\Passwords\Update::class => create(Http\Passwords\Update::class)
        ->constructor(get(DBAL\Connection::class)),
    
    Http\Actions\Resolve::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['graph'];

        return new Http\Actions\Resolve(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class),
            $settings['baseUrl']
        );
    }),

    Http\Actions\Add::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['graph'];
        
        return new Http\Actions\Add(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class),
            $settings['baseUrl']
        );
    }),

    Http\Actions\Update::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['graph'];
        
        return new Http\Actions\Update(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class),
            $settings['baseUrl']
        );
    }),

    Http\Actions\Remove::class => factory(function (ContainerInterface $c) {
        return new Http\Actions\Remove(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class)
        );
    }),

    Http\Actions\Relationships\Add::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['graph'];
        
        return new Http\Actions\Relationships\Add(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class),
            $settings['baseUrl']
        );
    }),

    Http\Actions\Relationships\Remove::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['graph'];
        
        return new Http\Actions\Relationships\Remove(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class),
            $settings['baseUrl']
        );
    }),

    Http\Actions\Relationships\Update::class => factory(function (ContainerInterface $c) {
        $settings = $c->get('settings')['graph'];
        
        return new Http\Actions\Relationships\Update(
            $c->get(DBAL\Connection::class),
            $c->get(Graph\Schema\Container::class),
            $settings['baseUrl']
        );
    }),
];
