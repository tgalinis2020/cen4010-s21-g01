<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;

use ThePetPark\Http\Resources\Posts;
use ThePetPark\Http\Resources\Users;
use ThePetPark\Http\Resources\Pets;
use ThePetPark\Services;

use function DI\factory;

/**
 * Most resources support the following operations:
 *  - fetch (collection of items)
 *  - fetch item
 *  - create item
 *  - update item
 */
return [

    // BEGIN (Users)
    
    Users\CreateItem::class => factory(function (ContainerInterface $c) {
        return new Users\CreateItem(
            $c->get(Connection::class)
        );
    }),

    // END (Users)


    // BEGIN(Posts)

    Posts\Fetch::class => factory(function (ContainerInterface $c) {
        return new Posts\Fetch(
            $c->get(Connection::class)
        );
    }),
    
    Posts\FetchItem::class => factory(function (ContainerInterface $c) {
        new Posts\FetchItem(
            $c->get(Connection::class)
        );
    }),

    Posts\CreateItem::class => factory(function (ContainerInterface $c) {
        return new Posts\CreateItem(
            $c->get(Connection::class)
        );
    }),

    Posts\UpdateItem::class => factory(function (ContainerInterface $c) {
        return new Posts\UpdateItem(
            $c->get(Connection::class)
        );
    }),

    // END(Posts)


    // BEGIN (Post\Comments)

    Posts\Comments\Fetch::class => factory(function (ContainerInterface $c) {
        return new Posts\Comments\Fetch(
            $c->get(Connection::class)
        );
    }),

    /*
    Post\Comments\FetchItem::class => factory(function (ContainerInterface $c) {
        return new Post\Comments\FetchItem(
            $c->get(Connection::class)
        );
    }),
    */

    Posts\Comments\CreateItem::class => factory(function (ContainerInterface $c) {
        return new Posts\Comments\CreateItem(
            $c->get(Connection::class)
        );
    }),

    Posts\Comments\UpdateItem::class => factory(function (ContainerInterface $c) {
        return new Posts\Comments\UpdateItem(
            $c->get(Connection::class)
        );
    }),

    // END (Post\Comments)
    

    // BEGIN (Pets)



    // END (Pets)

];

