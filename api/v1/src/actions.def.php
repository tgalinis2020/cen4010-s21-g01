<?php declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Actions;

return [
    Actions\HelloWorldAction::class => function (ContainerInterface $c) {
        return new Actions\HelloWorldAction();
    },

    Actions\FetchPostsAction::class => function (ContainerInterface $c) {
        // TODO: wire dependencies
        return new Actions\FetchPostsAction();
    },

    Actions\NewPostAction::class => function (ContainerInterface $c) {
        // TODO: wire dependencies
        return new Actions\NewPostAction();
    },
];
