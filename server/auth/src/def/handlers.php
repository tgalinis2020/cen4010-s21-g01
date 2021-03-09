<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use ThePetPark\Auth\Handlers;
use function DI\create;
use function DI\get;

return [

    Handlers\HelloWorldHandler::class => create(HandlersHelloWordHandler::class),

    Handlers\ShowLoginFormHandler::class => function (ContainerInterface $c) {
        return new Handlers\ShowLoginFormHandler();
    },

    Handlers\LoginHandler::class => function (ContainerInterface $c) {
        return new Handlers\LoginHandler();
    },

    Handlers\ShowRegistrationFormHandler::class => function (ContainerInterface $c) {
        return new Handlers\ShowRegistrationFormHandler();
    },

    Handlers\RegistrationHandler::class => function (ContainerInterface $c) {
        return new Handlers\RegistrationHandler();
    },
];

