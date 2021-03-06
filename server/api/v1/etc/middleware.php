<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use ThePetPark\Schema;
use ThePetPark\Services;
use ThePetPark\Filters;
use ThePetPark\Middleware\Auth;
use ThePetPark\Middleware\Features;

use function DI\create;
use function DI\factory;
use function DI\get;

return [

    Auth\Session::class => create(Auth\Session::class)
        ->constructor(get(Services\JWT\Decoder::class)),

    Auth\Guard::class => create(Auth\Guard::class),

    Auth\Guard\Permissive::class => create(Auth\Guard\Permissive::class),

    Auth\Guard\Protect::class => create(Auth\Guard\Protect::class)
        ->constructor(get(Connection::class), get(Schema\Container::class)),

    Features\Check::class => create(Features\Check::class)
        ->constructor(get(Schema\Container::class)),

    Features\CheckRelationship::class => create(Features\CheckRelationship::class)
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

    Features\Filtering::class => factory(function (ContainerInterface $c) {
        return new Features\Filtering([
            'eq' => new Filters\EqualTo(),
            'ne' => new Filters\NotEqualTo(),
            'lt' => new Filters\LessThan(),
            'le' => new Filters\LessThanOrEqualTo(),
            'gt' => new Filters\GreaterThan(),
            'ge' => new Filters\GreaterThanOrEqualTo(),
            'lk' => new Filters\Like(),
            'nl' => new Filters\NotLike(),
            'in' => new Filters\In(),
            'ni' => new Filters\NotIn(),
        ]);
    }),

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
