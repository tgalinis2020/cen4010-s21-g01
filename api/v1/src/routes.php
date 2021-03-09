<?php declare(strict_types=1);

use ThePetPark\Actions;

/**
 * Bind API endpoints to actions (a.k.a. controllers)
 *
 * The provided actions must be defined in the app's dependency container.
 */
return function (\Slim\App $app) {
    $app->map(['GET'], '/hello[/{name}]', Actions\HelloWorldAction::class);
    $app->map(['GET'], '/posts', Actions\FetchPostsAction::class);  
    $app->map(['POST'], '/posts', Actions\NewPostAction::class);
};
