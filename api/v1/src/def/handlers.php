<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Handlers\Http;

return [
    Http\HelloWorldHandler::class => function (ContainerInterface $c) {
        return new Http\HelloWorldHandler();
    },

    Actions\FetchPostsAction::class => function (ContainerInterface $c) {
        return new Http\FetchPostsHandler();
    },

    Actions\CreatePostAction::class => function (ContainerInterface $c) {
        return new Http\CreatePostHandler();
    },
];
