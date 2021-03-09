<?php

declare(strict_types=1);

use ThePetPark\Handlers\Http;

/**
 * Map API endpoints to handlers (a.k.a. controllers)
 *
 * The provided handlers must be defined in the app's dependency container.
 */
return function (\Slim\App $app) {
    $app->map(['GET'], '/hello[/{name}]', Http\HelloWorldHandler::class);
    $app->map(['GET'], '/posts', Http\FetchPostsHandler::class);  
    $app->map(['POST'], '/posts', Http\CreatePostHandler::class);
};
