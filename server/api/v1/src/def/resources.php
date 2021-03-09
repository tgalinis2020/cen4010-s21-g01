<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Resources\HelloWorld;
use ThePetPark\Resources\Posts;
use ThePetPark\Resources\Comments;
use function DI\create;
use function DI\get;

/**
 * Each resource supports the following operations:
 *  - fetch (collection of items)
 *  - fetch item
 *  - create item
 *  - update item
 */
return [

    HelloWorld::class => create(HelloWord::class),


    Posts\Fetch::class => create(Posts\Fetch::class),

    Posts\FetchItem::class => create(Posts\FetchItem::class),

    Posts\CreateItem::class => create(Posts\CreateItem::class),

    Posts\UpdateItem::class => create(Posts\UpdateItem::class),


    Comments\Fetch::class => create(Comments\Fetch::class),

    Comments\FetchItem::class => create(Comments\FetchItem::class),

    Comments\CreateItem::class => create(Comments\CreateItem::class),

    Comments\UpdateItem::class => create(Comments\UpdateItem::class),

];

