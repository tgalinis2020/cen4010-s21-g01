<?php

declare(strict_types=1);

use ThePetPark\Auth\Handlers;

/**
 * Map request handlers to endpoints.
 *
 * The provided handlers must be defined in the app's dependency container.
 */
return function (Slim\App $app) {

    $app->map(['GET'], '/hello[/{name}]', Handlers\HelloWorldHandler::class)
        ->setName('hello');

    $app->map(['GET'], '/session', Handlers\EchoSessionHandler::class)
        ->setName('session');

    $app->group('/login', function () {
        $this->map(['GET'],  '', Handlers\ShowLoginFormHandler::class)
            ->setName('login');

        $this->map(['POST'], '', Handlers\LoginHandler::class);
    });

    $this->map(['GET'],  '/logout', Handlers\LogoutHandler::class)
        ->setName('logout');

    $app->group('/register', function () {
        $this->map(['GET'],  '', Handlers\ShowRegistrationFormHandler::class)
            ->setName('register');

        $this->map(['POST'], '', Handlers\RegistrationHandler::class);
    });

};
