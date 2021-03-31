<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL;
use Doctrine\DBAL\Connection;
use ThePetPark\Http;
use ThePetPark\Services;
use ThePetPark\Middleware;
use ThePetPark\Middleware\Features;
use ThePetPark\Library\Graph\Schema;

use function DI\create;
use function DI\factory;
use function DI\get;

return [

    Middleware\Session::class => create(Middleware\Session::class)
        ->constructor(get(Services\JWT\Decoder::class)),

    Features\Check::class => create(Features\Check::class)
        ->constructor(get(Schema\Container::class)),
    
    Features\Initialization::class => factory(function (ContainerInterface $c) {
        return new Features\Initialization(
            $c->get(Connection::class),
            $c->get(Schema\Container::class),
            $c->get('settings')['baseUrl']
        );
    }),

    Features\Resolver::class => create(Features\Resolver::class),

    Features\SparseFieldsets::class => create(Features\SparseFieldsets::class)
        ->constructor(get(Schema\Container::class)),

    Features\Filtering::class => create(Features\Filtering::class),

    Features\Sorting::class => create(Features\Sorting::class),

    Features\ParseIncludes::class => factory(function (ContainerInterface $c) {
        return new Features\ParseIncludes($c->get('settings')['baseUrl']);
    }),

    Features\Pagination\CursorBased::class => factory(function (ContainerInterface $c) {
        return new Features\Pagination\CursorBased($c->get('settings')['defaultPageSize']);
    }),

    Features\Pagination\PageBased::class => factory(function (ContainerInterface $c) {
        return new Features\Pagination\PageBased($c->get('settings')['defaultPageSize']);
    }),

    Features\Pagination\OffsetBased::class => factory(function (ContainerInterface $c) {
        return new Features\Pagination\OffsetBased($c->get('settings')['defaultPageSize']);
    }),

    Features\Pagination\Links\CursorBased::class => factory(function (ContainerInterface $c) {
        return new Features\Pagination\Links\CursorBased($c->get('settings')['baseUrl']);
    }),

    Features\Pagination\Links\PageBased::class => factory(function (ContainerInterface $c) {
        return new Features\Pagination\Links\PageBased($c->get('settings')['baseUrl']);
    }),

    Features\Pagination\Links\OffsetBased::class => factory(function (ContainerInterface $c) {
        return new Features\Pagination\Links\OffsetBased($c->get('settings')['baseUrl']);
    }),
];
