<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Http\Resources\Posts;
use ThePetPark\Http\Resources\Comments;
use Doctrine\DBAL\Connection;

use function DI\factory;

/**
 * Most resources support the following operations:
 *  - fetch (collection of items)
 *  - fetch item
 *  - create item
 *  - update item
 */
return [

    // BEGIN(Posts)

    Posts\Fetch::class => factory(function (ContainerInterface $c) {
        return new Posts\Fetch($c->get(Connection::class));
    }),
    
    Posts\FetchItem::class => factory(function (ContainerInterface $c) {
        new Posts\FetchItem($c->get(Connection::class));
    }),

    Posts\CreateItem::class => factory(function (ContainerInterface $c) {
        return new Posts\CreateItem($c->get(Connection::class));
    }),

    Posts\UpdateItem::class => factory(function (ContainerInterface $c) {
        return new Posts\UpdateItem($c->get(Connection::class));
    }),

    // END(Posts)


    // BEGIN (Comments)

    Comments\Fetch::class => factory(function (ContainerInterface $c) {
        return new Comments\Fetch($c->get(Connection::class));
    }),

    Comments\FetchItem::class => factory(function (ContainerInterface $c) {
        return new Comments\FetchItem($c->get(Connection::class));
    }),

    Comments\CreateItem::class => factory(function (ContainerInterface $c) {
        return new Comments\CreateItem($c->get(Connection::class));
    }),

    Comments\UpdateItem::class => factory(function (ContainerInterface $c) {
        return new Comments\UpdateItem($c->get(Connection::class));
    }),

    // END (Comments)

];

